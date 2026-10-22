<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Finance\AfterSaleRequest;
use App\Models\FlightReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

trait HandlesAfterSaleRequests
{
    public function reservationsAfterSaleStore(Request $request, int $id)
    {
        $reservation = $this->findReservationForAfterSale($id);
        $data = $request->validate([
            'type' => ['required', Rule::in([
                AfterSaleRequest::TYPE_VOID_NO_PENALTY,
                AfterSaleRequest::TYPE_CANCEL_WITH_PENALTY,
                AfterSaleRequest::TYPE_CHANGE,
            ])],
            'change_kind' => ['nullable', Rule::in(['name', 'upgrade'])],
            'penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'penalty_currency' => ['nullable', 'string', 'size:3'],
            'note' => ['nullable', 'string', 'max:2000'],
            'new_ticket' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($data['type'] === AfterSaleRequest::TYPE_CHANGE && empty($data['change_kind'])) {
            return back()->withInput()->with('error', 'Select change type (name or upgrade).');
        }

        $path = null;
        if ($request->hasFile('new_ticket')) {
            $path = $request->file('new_ticket')->store('after-sale-tickets', 'public');
        }

        AfterSaleRequest::query()->create([
            'flight_reservation_id' => $reservation->id,
            'tenant_id' => $reservation->tenant_id,
            'requested_by' => Auth::id(),
            'type' => $data['type'],
            'status' => AfterSaleRequest::STATUS_PENDING,
            'change_kind' => $data['change_kind'] ?? null,
            'penalty_amount' => $data['type'] === AfterSaleRequest::TYPE_CANCEL_WITH_PENALTY
                ? ($data['penalty_amount'] ?? 0)
                : null,
            'penalty_currency' => $data['penalty_currency'] ?? $reservation->currency,
            'note' => $data['note'] ?? null,
            'new_ticket_path' => $path,
        ]);

        return back()->with('success', 'After-sale request submitted.');
    }

    public function reservationsAfterSaleStatus(Request $request, int $id, int $requestId)
    {
        $user = Auth::user();
        if (! $user || ($user->user_type !== 'super_admin' && ! $user->hasPermission('finance.manage'))) {
            abort(403);
        }

        $reservation = FlightReservation::query()->findOrFail($id);
        $afterSale = AfterSaleRequest::query()
            ->where('flight_reservation_id', $reservation->id)
            ->whereKey($requestId)
            ->firstOrFail();

        $data = $request->validate([
            'status' => ['required', Rule::in([
                AfterSaleRequest::STATUS_PENDING,
                AfterSaleRequest::STATUS_APPROVED,
                AfterSaleRequest::STATUS_REJECTED,
                AfterSaleRequest::STATUS_COMPLETED,
            ])],
        ]);
        $afterSale->update(['status' => $data['status']]);

        return back()->with('success', 'After-sale status updated.');
    }

    protected function findReservationForAfterSale(int $id): FlightReservation
    {
        if (method_exists($this, 'findReservationForTicketPdf')) {
            return $this->findReservationForTicketPdf($id);
        }
        if (method_exists($this, 'findPublicAccessibleReservation')) {
            return $this->findPublicAccessibleReservation($id);
        }

        return FlightReservation::query()->findOrFail($id);
    }
}
