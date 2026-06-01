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
    private function passengers(int $adults, array $x): string
    {
        $xml = '';
        for ($i = 0; $i < max(1, min(9, $adults)); $i++) {
            $xml .= "\n      <com:SearchPassenger Code=\"ADT\"/>";
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
        $adults = (int) ($params['adults'] ?? 1);

        if ($solutionXml === '') {
            return '';
        }

        $body = <<<XML
    <air:AirPriceReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$solutionXml}
      <air:AirPricingCommand/>
{$this->passengers($adults, $x)}
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
        $fareBasis = $this->esc((string) ($params['fare_basis'] ?? ''));
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));

        $body = <<<XML
    <air:AirFareRulesReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirFareRulesKey FareInfoRef="1" FareBasis="{$fareBasis}" Origin="{$origin}" Destination="{$destination}"/>
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

        $body = <<<XML
    <air:SeatMapReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirSegment Carrier="{$carrier}" FlightNumber="{$flight}" Origin="{$origin}" Destination="{$destination}" DepartureTime="{$departure}" ProviderCode="{$x['gds']}"/>
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
