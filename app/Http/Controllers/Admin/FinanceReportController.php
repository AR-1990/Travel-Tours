<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Support\AccountStatementReport;
use App\Support\FlightSalesReport;
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
            ->with(['tenant', 'creator', 'reservation'])
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
            ->with(['tenant', 'user'])
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
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(sell_total),0) as sell_sum, COALESCE(SUM(markup_amount),0) as markup_sum, COALESCE(SUM(agent_profit),0) as profit_sum, COALESCE(SUM(service_fee),0) as svc_sum, COALESCE(SUM(commission_amount),0) as comm_sum')
            ->first();

        $pageTotals = $this->salesPageTotals($sales);

        return view('admin.finance.reports.sales', compact(
            'sales', 'tenants', 'from', 'to', 'tenantId', 'status', 'airline', 'totals', 'pageTotals'
        ));
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, FlightReservation>|\Illuminate\Support\Collection<int, FlightReservation>  $sales
     * @return array{total: float, price: float, tax: float, fuel: float, svc: float, comm: float, markup: float, profit: float}
     */
    protected function salesPageTotals($sales): array
    {
        $acc = [
            'total' => 0.0,
            'price' => 0.0,
            'tax' => 0.0,
            'fuel' => 0.0,
            'svc' => 0.0,
            'comm' => 0.0,
            'markup' => 0.0,
            'profit' => 0.0,
        ];
        foreach ($sales as $r) {
            $acc['total'] += (float) ($r->salesTotalAmount() ?? 0);
            $acc['price'] += (float) ($r->salesBaseAmount() ?? 0);
            $acc['tax'] += (float) ($r->salesTaxAmount() ?? 0);
            $acc['fuel'] += (float) ($r->salesFuelOtherAmount() ?? 0);
            $acc['svc'] += (float) ($r->salesServiceFeeAmount() ?? 0);
            $acc['comm'] += (float) ($r->salesCommissionAmount() ?? 0);
            $acc['markup'] += (float) ($r->salesMarkupAmount() ?? 0);
            $acc['profit'] += (float) ($r->salesProfitAmount() ?? 0);
        }

        return $acc;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, WalletTransaction>  $rows
     */
    protected function exportCsv($rows, string $basename): StreamedResponse
    {
        $filename = $basename.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, AccountStatementReport::csvHeaders(true));
            foreach ($rows as $tx) {
                fputcsv($out, AccountStatementReport::csvRow($tx, true));
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
            fputcsv($out, FlightSalesReport::csvHeaders(true));
            foreach ($rows as $r) {
                fputcsv($out, FlightSalesReport::csvRow($r, true));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
