<?php

namespace App\Support;

class TravelportUserMessage
{
    public static function from(string $operation, string $technicalMessage, ?string $responseBody = null): string
    {
        $body = (string) $responseBody;
        $technical = self::stripSchemaSuffix($technicalMessage);

        $segmentMessage = self::segmentAvailabilityMessage($body, $technical);
        if ($segmentMessage !== null) {
            return $segmentMessage;
        }

        $haystack = strtolower($technical.' '.$body);

        foreach (self::phraseMap() as $needle => $friendly) {
            if (str_contains($haystack, strtolower($needle))) {
                return $friendly;
            }
        }

        return match ($operation) {
            'air_create_reservation' => 'We could not complete the booking. Please search again and try another flight or date.',
            'air_ticketing' => 'We could not issue the ticket. Please try again or contact your travel agent.',
            'air_price' => 'We could not confirm this fare. Please search again and select another option.',
            'low_fare_search' => 'Flight search failed. Please try different dates or airports.',
            default => 'Something went wrong with the flight request. Please try again.',
        };
    }

    private static function stripSchemaSuffix(string $message): string
    {
        return trim(preg_replace('/\s*\(v\d+\)\s*$/', '', trim($message)) ?? trim($message));
    }

    private static function segmentAvailabilityMessage(string $body, string $technical): ?string
    {
        $errorCode = null;
        if (preg_match('/<(?:[\w]+:)?ErrorMessage>\*?([^<*]+)\*?<\/(?:[\w]+:)?ErrorMessage>/i', $body, $m)) {
            $errorCode = trim($m[1]);
        }

        $carrier = $flight = $origin = $destination = null;
        if (preg_match('/<(?:[\w]+:)?AirSegment\b[^>]*\bCarrier="([^"]*)"[^>]*\bFlightNumber="([^"]*)"[^>]*\bOrigin="([^"]*)"[^>]*\bDestination="([^"]*)"/i', $body, $seg)) {
            [$carrier, $flight, $origin, $destination] = [$seg[1], $seg[2], $seg[3], $seg[4]];
        }

        $flightLabel = ($carrier && $flight && $origin && $destination)
            ? "{$carrier} {$flight} ({$origin} → {$destination})"
            : null;

        $reason = null;
        $code = strtoupper((string) $errorCode);

        if (str_contains($code, 'WL CLOSED') || str_contains($code, '0 AVAIL')) {
            $reason = 'has no seats available';
        } elseif (str_contains($code, 'WL OPEN')) {
            $reason = 'is waitlist only (no confirmed seats)';
        }

        if ($reason !== null) {
            return $flightLabel
                ? "Flight {$flightLabel} {$reason}. Please search again and choose another option."
                : 'This flight has no confirmed seats available. Please search again and choose another option.';
        }

        if (str_contains(strtolower($technical), 'not bookable') || str_contains(strtolower($body), 'not bookable')) {
            return $flightLabel
                ? "Flight {$flightLabel} is no longer available for booking. Please search again and book another fare soon after pricing."
                : 'One or more flights are no longer available for booking. Please search again and book soon after pricing.';
        }

        if (str_contains(strtolower($technical), 'general air service error')) {
            return $flightLabel
                ? "Flight {$flightLabel} cannot be booked right now. Please try another date or airline."
                : 'This flight cannot be booked right now. Please try another date or airline.';
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private static function phraseMap(): array
    {
        return [
            'record locator not found' => 'Your booking reference was not found. Please search, price, and book again in the same session.',
            'invalid fop type' => 'There was a payment setup problem. Please select Cash and try again.',
            'filed fare has been invalidated' => 'The fare expired before ticketing. Please search and book again, then issue the ticket immediately.',
            'has no tickets yet' => 'The ticket is not ready yet. Wait a moment and try again, or search and book a new fare.',
            'unsuccessful primary host transaction' => 'The airline could not confirm this booking. Please try a different flight or contact support.',
            'pricing session expired' => 'Your pricing session expired. Please search and price again before booking.',
            'run air price first' => 'Please price the flight again before booking.',
            'connection error' => 'Could not reach the flight system. Please check your connection and try again.',
            'credentials are not configured' => 'Flight booking is not configured. Please contact the agency.',
            'target branch is required' => 'Flight API is not fully configured. Please contact the agency.',
        ];
    }
}
