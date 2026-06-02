<?php

namespace App\Services\Travelport;

use Illuminate\Support\Str;

class TravelportAirXmlBuilder
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function build(string $operation, array $params, int $schemaVer): ?string
    {
        return match ($operation) {
            'low_fare_search' => $this->lowFareSearch($params, $schemaVer),
            'availability_search' => $this->availabilitySearch($params, $schemaVer),
            'air_fare_display' => $this->airFareDisplay($params, $schemaVer),
            'flight_time_table' => $this->flightTimeTable($params, $schemaVer),
            'air_price' => $this->airPrice($params, $schemaVer),
            'air_fare_rules' => $this->airFareRules($params, $schemaVer),
            'seat_map' => $this->seatMap($params, $schemaVer),
            'universal_record_retrieve' => $this->universalRecordRetrieve($params, $schemaVer),
            'universal_record_cancel' => $this->universalRecordCancel($params, $schemaVer),
            'air_ticketing' => $this->airTicketing($params, $schemaVer),
            'air_retrieve_document' => $this->airRetrieveDocument($params, $schemaVer),
            default => $this->genericAirRequest($operation, $params, $schemaVer),
        };
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function envelope(string $requestXml, int $schemaVer, string $service = 'air'): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
  <soapenv:Header/>
  <soapenv:Body>
{$requestXml}
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * @return array{air: string, com: string, univ: string, target: string, origin: string, gds: string, trace: string}
     */
    private function ctx(int $schemaVer, ?string $tracePrefix = null): array
    {
        $c = TravelportIntegrationConfig::merged();
        $prefix = $tracePrefix ?? 'air';

        return [
            'air' => 'http://www.travelport.com/schema/air_v'.$schemaVer.'_0',
            'com' => 'http://www.travelport.com/schema/common_v'.$schemaVer.'_0',
            'univ' => 'http://www.travelport.com/schema/universal_v'.$schemaVer.'_0',
            'target' => $this->esc((string) ($c['target_branch'] ?? '')),
            'origin' => $this->esc((string) ($c['origin_application'] ?? 'UAPI')) ?: 'UAPI',
            'gds' => $this->esc((string) ($c['gds'] ?? '1G')) ?: '1G',
            'trace' => $this->esc($prefix.'-'.Str::lower(Str::random(10))),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function routeLegs(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer);
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $departure = $this->esc($this->date((string) ($params['departure_date'] ?? '')));
        $returnDate = $this->date((string) ($params['return_date'] ?? ''));

        $legs = $this->leg($origin, $destination, $departure, $x);
        if ($returnDate !== '') {
            $legs .= $this->leg($destination, $origin, $this->esc($returnDate), $x);
        }

        return $legs;
    }

    private function leg(string $origin, string $destination, string $departure, array $x): string
    {
        return <<<XML
      <air:SearchAirLeg>
        <air:SearchOrigin><com:CityOrAirport Code="{$origin}" PreferCity="true"/></air:SearchOrigin>
        <air:SearchDestination><com:CityOrAirport Code="{$destination}" PreferCity="true"/></air:SearchDestination>
        <air:SearchDepTime PreferredTime="{$departure}"/>
      </air:SearchAirLeg>
XML;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function passengers(int $adults, array $x, bool $withKeys = false): string
    {
        $xml = '';
        for ($i = 0; $i < max(1, min(9, $adults)); $i++) {
            $keyAttr = $withKeys ? ' Key="PAX'.($i + 1).'"' : '';
            $xml .= "\n      <com:SearchPassenger{$keyAttr} Code=\"ADT\"/>";
        }

        return $xml;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function lowFareSearch(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'lfs');
        $adults = (int) ($params['adults'] ?? 1);
        $legs = $this->routeLegs($params, $schemaVer);

        $body = <<<XML
    <air:LowFareSearchReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" SolutionResult="true" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$legs}
      <air:AirSearchModifiers MaxSolutions="50">
        <air:PreferredProviders><com:Provider Code="{$x['gds']}"/></air:PreferredProviders>
      </air:AirSearchModifiers>{$this->passengers($adults, $x)}
    </air:LowFareSearchReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function availabilitySearch(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'avail');
        $adults = (int) ($params['adults'] ?? 1);
        $legs = $this->routeLegs($params, $schemaVer);

        $body = <<<XML
    <air:AvailabilitySearchReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$legs}
      <air:AirSearchModifiers>
        <air:PreferredProviders><com:Provider Code="{$x['gds']}"/></air:PreferredProviders>
      </air:AirSearchModifiers>{$this->passengers($adults, $x)}
    </air:AvailabilitySearchReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airFareDisplay(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'fare');
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $departure = $this->date((string) ($params['departure_date'] ?? ''));
        $returnDate = $this->date((string) ($params['return_date'] ?? ''));
        $adults = max(1, min(9, (int) ($params['adults'] ?? 1)));

        $modifiers = ' MaxResponses="50" BaseFareOnly="false" UnrestrictedFaresOnly="false"';
        if ($departure !== '') {
            $modifiers .= ' DepartureDate="'.$this->esc($departure).'"';
        }
        if ($returnDate !== '') {
            $modifiers .= ' ReturnDate="'.$this->esc($returnDate).'"';
        }

        $passengers = '';
        for ($i = 0; $i < $adults; $i++) {
            $passengers .= "\n      <air:PassengerType Code=\"ADT\" PricePTCOnly=\"true\"/>";
        }

        $body = <<<XML
    <air:AirFareDisplayReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" Origin="{$origin}" Destination="{$destination}" ProviderCode="{$x['gds']}" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>{$passengers}
      <air:AirFareDisplayModifiers{$modifiers}/>
    </air:AirFareDisplayReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function flightTimeTable(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'tt');
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $startDate = $this->date((string) ($params['departure_date'] ?? ''));
        $endDate = $this->date((string) ($params['return_date'] ?? ''));
        if ($endDate === '' && $startDate !== '') {
            $endTs = strtotime($startDate.' +27 days');
            $endDate = $endTs ? date('Y-m-d', $endTs) : $startDate;
        }
        $departure = $this->esc($startDate);
        $end = $this->esc($endDate);

        $body = <<<XML
    <air:FlightTimeTableReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:FlightTimeTableCriteria>
        <air:GeneralTimeTable StartDate="{$departure}" EndDate="{$end}" IncludeConnection="false">
          <air:DaysOfOperation Sun="true" Mon="true" Tue="true" Wed="true" Thu="true" Fri="true" Sat="true"/>
          <air:FlightOrigin>
            <com:CityOrAirport Code="{$origin}"/>
          </air:FlightOrigin>
          <air:FlightDestination>
            <com:CityOrAirport Code="{$destination}"/>
          </air:FlightDestination>
        </air:GeneralTimeTable>
      </air:FlightTimeTableCriteria>
    </air:FlightTimeTableReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airPrice(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'price');
        $solutionXml = (string) ($params['_pricing_solution_xml'] ?? '');
        $lfsXml = (string) ($params['_lfs_xml'] ?? '');
        $adults = (int) ($params['adults'] ?? 1);

        if ($solutionXml === '') {
            return '';
        }

        $segmentRefs = self::extractSegmentRefsFromPricingSolution($solutionXml);
        $bookingInfo = self::extractBookingInfoFromPricingSolution($solutionXml);
        $itinerarySegments = $segmentRefs !== [] ? self::extractSegmentsByKey($lfsXml, $segmentRefs) : [];

        $airItineraryXml = '';
        $airPricingCommandXml = '';
        if ($itinerarySegments !== []) {
            $airItineraryXml = "\n      <air:AirItinerary>\n".implode("\n", array_map(fn ($seg) => '        '.$seg, $itinerarySegments))."\n      </air:AirItinerary>";
            $mods = [];
            if ($bookingInfo !== []) {
                foreach ($bookingInfo as $bi) {
                    $segRef = $this->esc($bi['segment_ref'] ?? '');
                    $bCode = $this->esc($bi['booking_code'] ?? '');
                    if ($segRef === '') {
                        continue;
                    }
                    if ($bCode !== '') {
                        $mods[] = "        <air:AirSegmentPricingModifiers AirSegmentRef=\"{$segRef}\">\n          <air:PermittedBookingCodes>\n            <air:BookingCode Code=\"{$bCode}\"/>\n          </air:PermittedBookingCodes>\n        </air:AirSegmentPricingModifiers>";
                    } else {
                        $mods[] = '        <air:AirSegmentPricingModifiers AirSegmentRef="'.$segRef.'"/>';
                    }
                }
            } else {
                $mods = array_map(fn ($key) => '        <air:AirSegmentPricingModifiers AirSegmentRef="'.$this->esc($key).'"/>', $segmentRefs);
            }
            $airPricingCommandXml = "\n      <air:AirPricingCommand>\n".implode("\n", $mods)."\n      </air:AirPricingCommand>";
        }

        $body = <<<XML
    <air:AirPriceReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$airItineraryXml}
{$this->passengers($adults, $x, true)}
{$airPricingCommandXml}
    </air:AirPriceReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airFareRules(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'rules');
        $fareRuleKey = (string) ($params['_fare_rule_key_xml'] ?? '');

        if ($fareRuleKey === '') {
            $fareBasis = $this->esc((string) ($params['fare_basis'] ?? ''));
            $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
            $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
            $fareRuleKey = '      <air:FareRuleLookup FareBasis="'.$fareBasis.'" Origin="'.$origin.'" Destination="'.$destination.'" ProviderCode="'.$x['gds'].'"/>';
        }

        $body = <<<XML
    <air:AirFareRulesReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$fareRuleKey}
    </air:AirFareRulesReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function seatMap(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'seat');
        $carrier = $this->esc(strtoupper((string) ($params['carrier'] ?? '')));
        $flight = $this->esc((string) ($params['flight_number'] ?? ''));
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $departure = $this->esc((string) ($params['departure_time'] ?? $params['departure_date'] ?? ''));
        $segmentKey = $this->esc((string) ($params['segment_key'] ?? 'SEAT1'));
        $classOfService = $this->esc((string) ($params['class_of_service'] ?? 'Y'));

        $body = <<<XML
    <air:SeatMapReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" ReturnSeatPricing="false" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirSegment Key="{$segmentKey}" Group="0" Carrier="{$carrier}" FlightNumber="{$flight}" Origin="{$origin}" Destination="{$destination}" DepartureTime="{$departure}" ClassOfService="{$classOfService}" ProviderCode="{$x['gds']}"/>
    </air:SeatMapReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function universalRecordRetrieve(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'ur');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));

        $body = <<<XML
    <univ:UniversalRecordRetrieveReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" UniversalLocatorCode="{$locator}" xmlns:univ="{$x['univ']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
    </univ:UniversalRecordRetrieveReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function universalRecordCancel(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'urc');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));
        $version = $this->esc((string) ($params['version'] ?? '0'));

        $body = <<<XML
    <univ:UniversalRecordCancelReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" UniversalLocatorCode="{$locator}" Version="{$version}" xmlns:univ="{$x['univ']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
    </univ:UniversalRecordCancelReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airTicketing(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'tkt');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));

        $body = <<<XML
    <air:AirTicketingReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>
    </air:AirTicketingReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airRetrieveDocument(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'doc');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));

        $body = <<<XML
    <air:AirRetrieveDocumentReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>
    </air:AirRetrieveDocumentReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function genericAirRequest(string $operation, array $params, int $schemaVer): ?string
    {
        $op = TravelportAirCatalog::get($operation);
        if ($op === null) {
            return null;
        }

        $reqName = $op['request'] ?? null;
        if (! is_string($reqName) || $reqName === '') {
            return null;
        }

        $x = $this->ctx($schemaVer, 'gen');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));

        $extra = $locator !== ''
            ? "\n      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>"
            : '';

        $body = <<<XML
    <air:{$reqName} TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>{$extra}
    </air:{$reqName}>
XML;

        return $this->envelope($body, $schemaVer);
    }

    public static function extractPricingSolution(string $lfsXml, ?string $solutionKey = null): ?string
    {
        if ($solutionKey !== null && $solutionKey !== '') {
            $escaped = preg_quote($solutionKey, '/');
            if (preg_match(
                '/<(?:[\w]+:)?AirPricingSolution\b[^>]*\bKey="'.$escaped.'"[^>]*>.*?<\/(?:[\w]+:)?AirPricingSolution>/s',
                $lfsXml,
                $m
            )) {
                return '      '.$m[0];
            }
        }

        if (preg_match('/<(?:[\w]+:)?AirPricingSolution\b[^>]*>.*?<\/(?:[\w]+:)?AirPricingSolution>/s', $lfsXml, $m)) {
            return '      '.$m[0];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function extractSegmentRefsFromPricingSolution(string $pricingSolutionXml): array
    {
        if ($pricingSolutionXml === '') {
            return [];
        }

        if (! preg_match_all('/<(?:[\w]+:)?AirSegmentRef\b[^>]*\bKey="([^"]+)"/', $pricingSolutionXml, $m)) {
            return [];
        }

        return array_values(array_unique($m[1]));
    }

    /**
     * @return list<array{segment_ref: string, booking_code: string}>
     */
    public static function extractBookingInfoFromPricingSolution(string $pricingSolutionXml): array
    {
        if ($pricingSolutionXml === '') {
            return [];
        }

        if (! preg_match_all('/<(?:[\w]+:)?BookingInfo\b([^>]*)\/>/s', $pricingSolutionXml, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $list = [];
        foreach ($matches as $m) {
            $attrs = $m[1];
            $segmentRef = '';
            $bookingCode = '';
            if (preg_match('/\bSegmentRef="([^"]+)"/', $attrs, $a)) {
                $segmentRef = $a[1];
            }
            if (preg_match('/\bBookingCode="([^"]+)"/', $attrs, $a)) {
                $bookingCode = $a[1];
            }
            if ($segmentRef !== '') {
                $list[] = ['segment_ref' => $segmentRef, 'booking_code' => $bookingCode];
            }
        }

        return $list;
    }

    /**
     * @param  list<string>  $segmentKeys
     * @return list<string>
     */
    public static function extractSegmentsByKey(string $lfsXml, array $segmentKeys): array
    {
        if ($lfsXml === '' || $segmentKeys === []) {
            return [];
        }

        $segments = [];
        foreach ($segmentKeys as $key) {
            $escaped = preg_quote($key, '/');
            if (preg_match('/<(?:[\w]+:)?AirSegment\b[^>]*\bKey="'.$escaped.'"[^>]*\/>/s', $lfsXml, $m)) {
                $segments[] = self::normalizeSegmentForAirPrice($m[0]);
                continue;
            }

            if (preg_match('/<(?:[\w]+:)?AirSegment\b[^>]*\bKey="'.$escaped.'"[^>]*>.*?<\/(?:[\w]+:)?AirSegment>/s', $lfsXml, $m)) {
                $segments[] = self::normalizeSegmentForAirPrice($m[0]);
            }
        }

        return $segments;
    }

    private static function normalizeSegmentForAirPrice(string $segmentXml): string
    {
        if (! preg_match('/<(?:[\w]+:)?AirSegment\b([^>]*)>/s', $segmentXml, $m)) {
            return $segmentXml;
        }

        $attrs = $m[1];
        $keep = [
            'Key', 'Group', 'Carrier', 'FlightNumber', 'Origin', 'Destination',
            'DepartureTime', 'ArrivalTime', 'ClassOfService', 'ProviderCode',
        ];

        $parts = [];
        foreach ($keep as $name) {
            if (preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/', $attrs, $a)) {
                $parts[] = $name.'="'.htmlspecialchars($a[1], ENT_XML1 | ENT_QUOTES, 'UTF-8').'"';
            }
        }

        if (! preg_match('/\bProviderCode="/', implode(' ', $parts))
            && preg_match('/<(?:[\w]+:)?AirAvailInfo\b[^>]*\bProviderCode="([^"]+)"/', $segmentXml, $p)
        ) {
            $parts[] = 'ProviderCode="'.htmlspecialchars($p[1], ENT_XML1 | ENT_QUOTES, 'UTF-8').'"';
        }

        return '<air:AirSegment '.implode(' ', $parts).'/>';
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function date(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $ts = strtotime($date);

        return $ts ? date('Y-m-d', $ts) : $date;
    }
}
