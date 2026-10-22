<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\CurrencyRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrencyRateController extends Controller
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
        $rates = CurrencyRate::query()->with('updater')->orderBy('base_currency')->orderBy('quote_currency')->paginate(50);

        return view('admin.finance.currency-rates.index', compact('rates'));
    }

    public function store(Request $request)
    {
        $this->authorizeFinance();
        $data = $request->validate([
            'base_currency' => ['required', 'string', 'size:3'],
            'quote_currency' => ['required', 'string', 'size:3', 'different:base_currency'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CurrencyRate::query()->updateOrCreate(
            [
                'base_currency' => strtoupper($data['base_currency']),
                'quote_currency' => strtoupper($data['quote_currency']),
            ],
            [
                'rate' => $data['rate'],
                'notes' => $data['notes'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Currency rate saved.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeFinance();
        $rate = CurrencyRate::query()->findOrFail($id);
        $data = $request->validate([
            'rate' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $rate->update([
            'rate' => $data['rate'],
            'notes' => $data['notes'] ?? null,
            'is_active' => $request->boolean('is_active', $rate->is_active),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Currency rate updated.');
    }

    public function destroy(int $id)
    {
        $this->authorizeFinance();
        CurrencyRate::query()->whereKey($id)->delete();

        return back()->with('success', 'Currency rate deleted.');
    }
}
