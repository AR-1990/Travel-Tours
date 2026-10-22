<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Models\System\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    protected function authorizeReports(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        if ($user->user_type === 'super_admin'
            || $user->hasPermission('finance.reports')
            || $user->hasPermission('finance.manage')) {
            return;
        }
        abort(403, 'Finance reports permission required.');
    }

    public function statement(Request $request)
    {
        $this->authorizeReports();

        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $tenantId = $request->filled('tenant_id') ? (int) $request->tenant_id : null;

        $query = WalletTransaction::query()
            ->with(['tenant', 'creator'])
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderByDesc('id');

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get(), 'account-statement');
        }

        $transactions = $query->paginate(50)->withQueryString();
        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name', 'agency_code']);

        $summary = WalletTransaction::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("type, SUM(CASE WHEN direction='debit' THEN amount ELSE 0 END) as debit_total, SUM(CASE WHEN direction='credit' THEN amount ELSE 0 END) as credit_total")
            ->groupBy('type')
            ->get();

        return view('admin.finance.reports.statement', compact(
            'transactions', 'tenants', 'from', 'to', 'tenantId', 'summary'
        ));
    }

    public function sales(Request $request)
    {
        $this->authorizeReports();

        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $tenantId = $request->filled('tenant_id') ? (int) $request->tenant_id : null;
        $status = $request->input('status');
        $airline = strtoupper(trim((string) $request->input('airline', '')));

        $query = FlightReservation::query()
            ->with('tenant')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($airline !== '', fn ($q) => $q->where('carrier', $airline))
            ->whereDate('booked_at', '>=', $from)
            ->whereDate('booked_at', '<=', $to)
            ->orderByDesc('id');

        if ($request->input('export') === 'csv') {
            return $this->exportSalesCsv($query->get());
        }

        $sales = $query->paginate(50)->withQueryString();
        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name', 'agency_code']);

        $totals = FlightReservation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($airline !== '', fn ($q) => $q->where('carrier', $airline))
            ->whereDate('booked_at', '>=', $from)
            ->whereDate('booked_at', '<=', $to)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(sell_total),0) as sell_sum, COALESCE(SUM(markup_amount),0) as markup_sum, COALESCE(SUM(agent_profit),0) as profit_sum')
            ->first();

        return view('admin.finance.reports.sales', compact(
            'sales', 'tenants', 'from', 'to', 'tenantId', 'status', 'airline', 'totals'
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, WalletTransaction>  $rows
     */
    protected function exportCsv($rows, string $basename): StreamedResponse
    {
        $filename = $basename.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'date', 'agency', 'type', 'direction', 'debit', 'credit', 'balance', 'pnr', 'order', 'vendor', 'description', 'authority']);
            foreach ($rows as $tx) {
                fputcsv($out, [
                    $tx->id,
                    optional($tx->created_at)?->toDateTimeString(),
                    $tx->tenant?->name,
                    $tx->type,
                    $tx->direction,
                    $tx->direction === 'debit' ? $tx->amount : 0,
                    $tx->direction === 'credit' ? $tx->amount : 0,
                    $tx->balance_after,
                    $tx->pnr,
                    $tx->order_ref,
                    $tx->vendor,
                    $tx->description,
                    $tx->creator?->email,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FlightReservation>  $rows
     */
    protected function exportSalesCsv($rows): StreamedResponse
    {
        $filename = 'sales-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'booked_at', 'status', 'agency', 'pnr', 'route', 'airline', 'provider', 'fare', 'sell_total', 'markup', 'profit', 'payment', 'pax']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    optional($r->booked_at)?->toDateTimeString(),
                    $r->status,
                    $r->tenant?->name,
                    $r->air_reservation_locator ?: $r->provider_locator,
                    $r->origin.'-'.$r->destination,
                    $r->carrier,
                    $r->provider(),
                    $r->base_price,
                    $r->sell_total,
                    $r->markup_amount,
                    $r->agent_profit,
                    $r->form_of_payment,
                    trim($r->passenger_first.' '.$r->passenger_last),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
