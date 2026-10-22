<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\Wallet;
use App\Models\Finance\WalletTransaction;
use App\Models\System\Tenant;
use App\Services\Finance\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $wallets
    ) {}

    protected function authorizeFinanceManage(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        if ($user->user_type === 'super_admin' || $user->hasPermission('finance.manage') || $user->hasPermission('finance.accounts')) {
            return;
        }
        abort(403, 'Finance permission required.');
    }

    public function index(Request $request)
    {
        $this->authorizeFinanceManage();

        $wallets = Wallet::query()
            ->with('tenant')
            ->when($request->filled('tenant_id'), fn ($q) => $q->where('tenant_id', (int) $request->tenant_id))
            ->orderByDesc('updated_at')
            ->paginate(30)
            ->withQueryString();

        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name', 'agency_code']);

        return view('admin.finance.wallets.index', compact('wallets', 'tenants'));
    }

    public function show(int $tenantId)
    {
        $this->authorizeFinanceManage();
        $tenant = Tenant::query()->findOrFail($tenantId);
        $wallet = $this->wallets->ensureWallet($tenant);
        $transactions = WalletTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->with('creator')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.finance.wallets.show', compact('tenant', 'wallet', 'transactions'));
    }

    public function topUp(Request $request, int $tenantId)
    {
        $this->authorizeFinanceManage();
        $tenant = Tenant::query()->findOrFail($tenantId);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->wallets->topUp($tenant, (float) $data['amount'], Auth::user(), [
                'description' => $data['description'] ?? 'Wallet top-up',
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet topped up.');
    }

    public function refund(Request $request, int $tenantId)
    {
        $this->authorizeFinanceManage();
        $tenant = Tenant::query()->findOrFail($tenantId);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:500'],
            'pnr' => ['nullable', 'string', 'max:32'],
        ]);

        try {
            $this->wallets->refund($tenant, (float) $data['amount'], Auth::user(), [
                'description' => $data['description'] ?? 'Wallet refund',
                'pnr' => $data['pnr'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Refund credited to wallet.');
    }

    public function reverse(int $transactionId)
    {
        $this->authorizeFinanceManage();
        $tx = WalletTransaction::query()->findOrFail($transactionId);

        try {
            $this->wallets->reverse($tx, Auth::user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transaction reversed.');
    }
}
