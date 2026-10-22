<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\Finance\TicketPdfService;
use Illuminate\Http\Response;

trait DownloadsFlightTicketPdf
{
    public function reservationsTicketPdf(int $id, TicketPdfService $tickets): Response
    {
        $reservation = $this->findReservationForTicketPdf($id);
        $withPrice = request()->has('with_price')
            ? request()->boolean('with_price')
            : ($reservation->print_with_price !== false);
        $binary = $tickets->pdfBinary($reservation, $withPrice);
        $filename = 'ticket-'.($reservation->air_reservation_locator ?: $reservation->id).($withPrice ? '' : '-noprice').'.pdf';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function reservationsTicketPrint(int $id, TicketPdfService $tickets)
    {
        $reservation = $this->findReservationForTicketPdf($id);
        $withPrice = request()->has('with_price')
            ? request()->boolean('with_price')
            : ($reservation->print_with_price !== false);

        return response($tickets->html($reservation, $withPrice), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    protected function findReservationForTicketPdf(int $id): FlightReservation
    {
        if (method_exists($this, 'findPublicAccessibleReservation')) {
            return $this->findPublicAccessibleReservation($id);
        }

        $reservation = FlightReservation::query()->findOrFail($id);
        $user = auth()->user();
        if ($user && $user->user_type === 'super_admin') {
            return $reservation;
        }
        if ($user && $user->tenant_id && (int) $reservation->tenant_id === (int) $user->tenant_id) {
            return $reservation;
        }
        if ($user && (int) $reservation->user_id === (int) $user->id) {
            return $reservation;
        }

        abort(403, 'You do not have access to this ticket.');
    }
}
