<?php

namespace App\Support;

use App\Models\FlightReservation;

/**
 * Shared sales-report row shape for admin + agency UI/CSV (reference: e-ticket, fare, tax, fees, route, agent).
 */
class FlightSalesReport
{
    /**
     * @return list<string>
     */
    public static function csvHeaders(bool $includeAgency = true): array
    {
        $headers = [
            'e_ticket',
            'pnr',
            'order_id',
            'airline',
            'status',
            'total',
            'price',
            'tax',
            'fuel_other',
            'svc_fee',
            'agency_comm',
            'markup',
            'profit',
            'curr',
            'passenger',
            'sold',
            'route',
            'agency_user',
            'departure',
            'arrival',
            'provider',
            'payment',
        ];

        if ($includeAgency) {
            array_splice($headers, 4, 0, ['agency']);
        }

        return $headers;
    }

    /**
     * @return list<string|int|float|null>
     */
    public static function csvRow(FlightReservation $r, bool $includeAgency = true): array
    {
        $row = [
            $r->ticketNumbersLabel(),
            $r->pnrLabel(),
            $r->id,
            $r->airlineSalesLabel(),
            $r->status,
            $r->salesTotalAmount(),
            $r->salesBaseAmount(),
            $r->salesTaxAmount(),
            $r->salesFuelOtherAmount(),
            $r->salesServiceFeeAmount(),
            $r->salesCommissionAmount(),
            $r->salesMarkupAmount(),
            $r->salesProfitAmount(),
            $r->salesCurrency(),
            $r->passengerName(),
            optional($r->booked_at)?->format('Y-m-d H:i'),
            $r->salesRouteLabel(),
            $r->salesAgentLabel(),
            optional($r->departure_date)?->format('Y-m-d'),
            $r->salesArrivalLabel(),
            $r->providerLabel(),
            $r->form_of_payment,
        ];

        if ($includeAgency) {
            array_splice($row, 4, 0, [$r->salesAgencyLabel()]);
        }

        return $row;
    }
}
