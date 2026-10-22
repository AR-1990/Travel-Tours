<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\AirlineFee;
use App\Models\System\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AirlineFeeController extends Controller
{
    protected function authorizeFinance(): void
    {
        $user = Auth::user();
        if (! $user || ($user->user_type !== 'super_admin' && ! $user->hasPermission('finance.manage'))) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeFinance();
        $fees = AirlineFee::query()->with('tenant')->orderBy('airline_code')->orderBy('label')->paginate(50);
        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.finance.airline-fees.index', compact('fees', 'tenants'));
    }

    public function store(Request $request)
    {
        $this->authorizeFinance();
        $data = $request->validate([
            'airline_code' => ['required', 'string', 'size:2'],
            'fee_type' => ['required', Rule::in(['service', 'ticketing', 'other'])],
            'label' => ['required', 'string', 'max:120'],
            'mode' => ['required', Rule::in(['fixed', 'percent'])],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AirlineFee::query()->create([
            'airline_code' => strtoupper($data['airline_code']),
            'fee_type' => $data['fee_type'],
            'label' => $data['label'],
            'mode' => $data['mode'],
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency']),
            'tenant_id' => $data['tenant_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Airline fee saved.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeFinance();
        $fee = AirlineFee::query()->findOrFail($id);
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'mode' => ['required', Rule::in(['fixed', 'percent'])],
            'amount' => ['required', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $fee->update([
            'label' => $data['label'],
            'mode' => $data['mode'],
            'amount' => $data['amount'],
            'is_active' => $request->boolean('is_active', $fee->is_active),
        ]);

        return back()->with('success', 'Airline fee updated.');
    }

    public function destroy(int $id)
    {
        $this->authorizeFinance();
        AirlineFee::query()->whereKey($id)->delete();

        return back()->with('success', 'Airline fee deleted.');
    }
}
