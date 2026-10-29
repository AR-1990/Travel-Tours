<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Services\Finance\WalletService;
use App\Support\AccountStatementReport;
use App\Support\FlightSalesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function __construct(
        protected WalletService $wallets
    ) {}

    protected function tenant(): Tenant
    {
        $user = Auth::user();
        if (! $user || ! $user->tenant_id) {
            abort(403, 'Agency account required.');
        }

        if (! in_array($user->user_type, ['tenant_admin', 'sub_agent', 'super_admin'], true)
            && ! $user->hasPermission('finance.reports')
            && ! $user->hasPermission('finance.accounts')) {
            abort(403, 'Finance permission required.');
        }

        return Tenant::query()->findOrFail((int) $user->tenant_id);
    }

    public function wallet()
    {
        $tenant = $this->tenant();
        $wallet = $this->wallets->ensureWallet($tenant);
        $transactions = WalletTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->with('creator')
            ->orderByDesc('id')
            ->paginate(50);

        return view('agent.finance.wallet', compact('tenant', 'wallet', 'transactions'));
    }

    public function statement(Request $request)
    {
        $tenant = $this->tenant();
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $query = WalletTransaction::query()
            ->with(['tenant', 'creator', 'reservation'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderByDesc('id');

        if ($request->input('export') === 'csv') {
            return $this->exportStatementCsv($query->get());
        }

        $transactions = $query->paginate(50)->withQueryString();
        $summary = WalletTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("type, SUM(CASE WHEN direction='debit' THEN amount ELSE 0 END) as debit_total, SUM(CASE WHEN direction='credit' THEN amount ELSE 0 END) as credit_total")
            ->groupBy('type')
            ->get();

        return view('agent.finance.statement', compact('tenant', 'transactions', 'from', 'to', 'summary'));
    }

    public function sales(Request $request)
    {
        $tenant = $this->tenant();
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $status = $request->input('status');
        $airline = strtoupper(trim((string) $request->input('airline', '')));

        $query = FlightReservation::query()
            ->with('user')
            ->where('tenant_id', $tenant->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($airline !== '', fn ($q) => $q->where('carrier', $airline))
            ->whereDate('booked_at', '>=', $from)
            ->whereDate('booked_at', '<=', $to)
            ->orderByDesc('id');

        if ($request->input('export') === 'csv') {
            return $this->exportSalesCsv($query->get());
        }

        $sales = $query->paginate(50)->withQueryString();
        $totals = FlightReservation::query()
            ->where('tenant_id', $tenant->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($airline !== '', fn ($q) => $q->where('carrier', $airline))
            ->whereDate('booked_at', '>=', $from)
            ->whereDate('booked_at', '<=', $to)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(sell_total),0) as sell_sum, COALESCE(SUM(markup_amount),0) as markup_sum, COALESCE(SUM(agent_profit),0) as profit_sum, COALESCE(SUM(service_fee),0) as svc_sum, COALESCE(SUM(commission_amount),0) as comm_sum')
            ->first();

        $pageTotals = [
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
            $pageTotals['total'] += (float) ($r->salesTotalAmount() ?? 0);
            $pageTotals['price'] += (float) ($r->salesBaseAmount() ?? 0);
            $pageTotals['tax'] += (float) ($r->salesTaxAmount() ?? 0);
            $pageTotals['fuel'] += (float) ($r->salesFuelOtherAmount() ?? 0);
            $pageTotals['svc'] += (float) ($r->salesServiceFeeAmount() ?? 0);
            $pageTotals['comm'] += (float) ($r->salesCommissionAmount() ?? 0);
            $pageTotals['markup'] += (float) ($r->salesMarkupAmount() ?? 0);
            $pageTotals['profit'] += (float) ($r->salesProfitAmount() ?? 0);
        }

        return view('agent.finance.sales', compact('tenant', 'sales', 'from', 'to', 'status', 'airline', 'totals', 'pageTotals'));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, WalletTransaction>  $rows
     */
    protected function exportStatementCsv($rows): StreamedResponse
    {
        $filename = 'agency-statement-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, AccountStatementReport::csvHeaders(false));
            foreach ($rows as $tx) {
                fputcsv($out, AccountStatementReport::csvRow($tx, false));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FlightReservation>  $rows
     */
    protected function exportSalesCsv($rows): StreamedResponse
    {
        $filename = 'agency-sales-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, FlightSalesReport::csvHeaders(false));
            foreach ($rows as $r) {
                fputcsv($out, FlightSalesReport::csvRow($r, false));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
