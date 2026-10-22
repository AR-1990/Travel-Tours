<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\TenantMarkup;
use App\Models\System\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MarkupController extends Controller
{
    protected function authorizeFinanceManage(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        if ($user->user_type === 'super_admin' || $user->hasPermission('finance.manage')) {
            return;
        }
        abort(403, 'Finance permission required.');
    }

    public function index()
    {
        $this->authorizeFinanceManage();
        $markups = TenantMarkup::query()->with('tenant')->orderBy('priority')->orderByDesc('id')->paginate(40);
        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.finance.markups.index', compact('markups', 'tenants'));
    }

    public function store(Request $request)
    {
        $this->authorizeFinanceManage();
        $data = $request->validate([
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'scope' => ['required', Rule::in([
                TenantMarkup::SCOPE_GLOBAL,
                TenantMarkup::SCOPE_PROVIDER,
                TenantMarkup::SCOPE_AIRLINE,
                TenantMarkup::SCOPE_AGENT,
            ])],
            'scope_key' => ['nullable', 'string', 'max:64'],
            'mode' => ['required', Rule::in([TenantMarkup::MODE_PERCENT, TenantMarkup::MODE_FIXED])],
            'value' => ['required', 'numeric'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (in_array($data['scope'], [TenantMarkup::SCOPE_AIRLINE, TenantMarkup::SCOPE_PROVIDER], true)
            && trim((string) ($data['scope_key'] ?? '')) === '') {
            return back()->withInput()->with('error', 'Scope key is required for airline/provider markup.');
        }

        TenantMarkup::query()->create([
            'tenant_id' => $data['tenant_id'] ?? null,
            'scope' => $data['scope'],
            'scope_key' => $data['scope'] === TenantMarkup::SCOPE_AIRLINE
                ? strtoupper((string) ($data['scope_key'] ?? ''))
                : strtolower((string) ($data['scope_key'] ?? '')),
            'mode' => $data['mode'],
            'value' => $data['value'],
            'priority' => $data['priority'] ?? 100,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Markup rule saved.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeFinanceManage();
        $markup = TenantMarkup::query()->findOrFail($id);
        $data = $request->validate([
            'mode' => ['required', Rule::in([TenantMarkup::MODE_PERCENT, TenantMarkup::MODE_FIXED])],
            'value' => ['required', 'numeric'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $markup->update([
            'mode' => $data['mode'],
            'value' => $data['value'],
            'priority' => $data['priority'] ?? $markup->priority,
            'is_active' => $request->boolean('is_active', $markup->is_active),
        ]);

        return back()->with('success', 'Markup updated.');
    }

    public function destroy(int $id)
    {
        $this->authorizeFinanceManage();
        TenantMarkup::query()->whereKey($id)->delete();

        return back()->with('success', 'Markup removed.');
    }
}
