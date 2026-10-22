<?php

namespace App\Services\Finance;

use App\Models\FlightReservation;
use App\Support\FlightDisplay;
use Illuminate\Support\Facades\View;

/**
 * Generates a printable ticket document. Without an external PDF library we
 * serve a print-ready HTML ticket (browser → Save as PDF) and a lightweight
 * text PDF fallback for download.
 */
class TicketPdfService
{
    public function html(FlightReservation $reservation, ?bool $withPrice = null): string
    {
        $data = $this->viewData($reservation, $withPrice);

        return View::make('pdf.flight-ticket', $data)->render();
    }

    /**
     * Minimal PDF (text + optional JPEG logo). Good enough for ticket download
     * when composer packages are unavailable.
     */
    public function pdfBinary(FlightReservation $reservation, ?bool $withPrice = null): string
    {
        $data = $this->viewData($reservation, $withPrice);
        $lines = [
            'ELECTRONIC TICKET / ITINERARY',
            'Agency: '.($data['agency'] ?? 'Wise Trust'),
            'Passenger: '.$data['passenger_name'],
            'Route: '.$data['origin'].' → '.$data['destination'],
            'PNR: '.$data['pnr'],
            'Airline: '.$data['airline_label'].' ('.$data['carrier'].')',
            'Status: '.strtoupper($data['status']),
            'Departure: '.$data['departure_date'],
        ];
        if (! empty($data['show_price'])) {
            $lines[] = 'Total: '.$data['currency'].' '.$data['total'];
            $lines[] = 'Payment: '.($data['form_of_payment'] ?: '—');
        } else {
            $lines[] = 'Fare: Not shown (agency print without price)';
        }
        $lines[] = 'Tickets: '.$data['ticket_numbers_label'];

        foreach ($data['segments'] as $seg) {
            $lines[] = sprintf(
                '%s%s %s-%s %s',
                $seg['carrier'] ?? '',
                $seg['flight_number'] ?? '',
                $seg['origin'] ?? '',
                $seg['destination'] ?? '',
                $seg['departure'] ?? ''
            );
        }

        return $this->buildSimplePdf($lines, $data['logo_path'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(FlightReservation $reservation, ?bool $withPrice = null): array
    {
        if ($withPrice === null) {
            $withPrice = $reservation->print_with_price !== false;
        }
        $solution = is_array($reservation->price_snapshot)
            ? ($reservation->price_snapshot['solutions'][0] ?? null)
            : null;
        $segments = [];
        if (is_array($reservation->itinerary)) {
            $segments = $reservation->itinerary['segments']
                ?? data_get($reservation->itinerary, 'journeys.0.segments', [])
                ?: [];
        }
        if ($segments === [] && is_array($solution)) {
            $segments = $solution['segments'] ?? data_get($solution, 'journeys.0.segments', []) ?: [];
        }

        $carrier = strtoupper((string) ($reservation->carrier ?: data_get($segments, '0.carrier', 'XX')));
        $price = FlightDisplay::parsePrice($reservation->sell_total ?? $reservation->total_price);
        $tickets = is_array($reservation->ticket_numbers) ? $reservation->ticket_numbers : [];
        $ticketLabel = $tickets === [] ? '—' : implode(', ', array_map('strval', $tickets));

        $logoPath = $this->airlineLogoPath($carrier);
        $tenant = $reservation->tenant;

        return [
            'reservation' => $reservation,
            'agency' => $tenant?->name ?: config('app.name', 'Wise Trust'),
            'passenger_name' => trim(($reservation->passenger_prefix ? $reservation->passenger_prefix.' ' : '').$reservation->passenger_first.' '.$reservation->passenger_last),
            'origin' => $reservation->origin,
            'destination' => $reservation->destination,
            'pnr' => (string) ($reservation->air_reservation_locator ?: $reservation->provider_locator ?: $reservation->universal_locator ?: '—'),
            'carrier' => $carrier,
            'airline_label' => FlightDisplay::airlineName($carrier),
            'status' => (string) $reservation->status,
            'departure_date' => optional($reservation->departure_date)?->format('d M Y') ?: '—',
            'return_date' => optional($reservation->return_date)?->format('d M Y'),
            'currency' => $reservation->currency ?: ($price['currency'] ?? 'USD'),
            'total' => $reservation->sell_total !== null
                ? number_format((float) $reservation->sell_total, 2)
                : ($price['amount'] ?? ($reservation->total_price ?: '—')),
            'form_of_payment' => $reservation->form_of_payment,
            'ticket_numbers_label' => $ticketLabel,
            'segments' => is_array($segments) ? $segments : [],
            'logo_path' => $logoPath,
            'logo_url' => $logoPath
                ? asset(ltrim(str_replace('\\', '/', substr($logoPath, strlen(public_path()))), '/'))
                : null,
            'show_price' => (bool) $withPrice,
        ];
    }

    protected function airlineLogoPath(string $carrier): ?string
    {
        $carrier = strtoupper($carrier);
        $candidates = [
            public_path('assets/img/airlines/'.strtolower($carrier).'.png'),
            public_path('assets/img/airlines/'.strtolower($carrier).'.jpg'),
            public_path('assets/img/flight/airline-1.png'),
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $lines
     */
    protected function buildSimplePdf(array $lines, ?string $jpegOrPngPath = null): string
    {
        $content = "BT /F1 11 Tf 50 780 Td 14 TL\n";
        foreach ($lines as $i => $line) {
            $safe = $this->pdfEscape($line);
            if ($i === 0) {
                $content .= "($safe) Tj T*\n";
            } else {
                $content .= "($safe) '\n";
            }
        }
        $content .= "ET\n";

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>";
        $objects[] = "<< /Length ".strlen($content)." >>\nstream\n{$content}endstream";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $obj) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref\n0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer<< /Size '.(count($objects) + 1).' /Root 1 0 R >>\n';
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    protected function pdfEscape(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
