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
            'low_fare_search_async' => $this->lowFareSearchAsync($params, $schemaVer),
            'availability_search' => $this->availabilitySearch($params, $schemaVer),
            'air_fare_display' => $this->airFareDisplay($params, $schemaVer),
            'flight_time_table' => $this->flightTimeTable($params, $schemaVer),
            'flight_details' => $this->flightDetails($params, $schemaVer),
            'flight_information' => $this->flightInformation($params, $schemaVer),
            'air_price' => $this->airPrice($params, $schemaVer),
            'air_fare_rules' => $this->airFareRules($params, $schemaVer),
            'seat_map' => $this->seatMap($params, $schemaVer),
            'air_reprice' => $this->airReprice($params, $schemaVer),
            'air_create_reservation' => $this->airCreateReservation($params, $schemaVer),
            'universal_record_retrieve' => $this->universalRecordRetrieve($params, $schemaVer),
            'universal_record_cancel' => $this->universalRecordCancel($params, $schemaVer),
            'universal_record_modify' => $this->universalRecordModify($params, $schemaVer),
            'air_ticketing' => $this->airTicketing($params, $schemaVer),
            'air_retrieve_document' => $this->airRetrieveDocument($params, $schemaVer),
            'air_void_ticket' => $this->airVoidTicket($params, $schemaVer),
            'air_refund_quote' => $this->airRefundQuote($params, $schemaVer),
            'air_refund' => $this->airRefund($params, $schemaVer),
            'air_cancel' => $this->airCancel($params, $schemaVer),
            'air_exchange_quote' => $this->airExchangeQuote($params, $schemaVer),
            'air_exchange' => $this->airExchange($params, $schemaVer),
            'air_exchange_ticketing' => $this->airExchangeTicketing($params, $schemaVer),
            'air_merchandising' => $this->airMerchandising($params, $schemaVer),
            'air_pre_pay' => $this->airPrePay($params, $schemaVer),
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
            'flig' => 'http://www.travelport.com/schema/flight_v'.$schemaVer.'_0',
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
    public function lowFareSearchAsync(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'lfsa');
        $adults = (int) ($params['adults'] ?? 1);
        $legs = $this->routeLegs($params, $schemaVer);

        $body = <<<XML
    <air:LowFareSearchAsynchReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" SolutionResult="true" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$legs}
{$this->passengers($adults, $x)}
      <air:AirSearchModifiers>
        <air:PreferredProviders>
          <com:Provider Code="{$x['gds']}"/>
        </air:PreferredProviders>
      </air:AirSearchModifiers>
    </air:LowFareSearchAsynchReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function flightDetails(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'fdet');
        $carrier = $this->esc(strtoupper((string) ($params['carrier'] ?? '')));
        $flight = $this->esc((string) ($params['flight_number'] ?? ''));
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $departure = $this->esc($this->date((string) ($params['departure_date'] ?? '')));

        $body = <<<XML
    <flig:FlightDetailsReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:flig="{$x['flig']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <flig:FlightDetailsCriteria>
        <flig:Carrier>{$carrier}</flig:Carrier>
        <flig:FlightNumber>{$flight}</flig:FlightNumber>
        <flig:Origin>{$origin}</flig:Origin>
        <flig:Destination>{$destination}</flig:Destination>
        <flig:DepartureDate>{$departure}</flig:DepartureDate>
      </flig:FlightDetailsCriteria>
    </flig:FlightDetailsReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function flightInformation(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'finfo');
        $carrier = $this->esc(strtoupper((string) ($params['carrier'] ?? '')));
        $flight = $this->esc((string) ($params['flight_number'] ?? ''));
        $origin = $this->esc(strtoupper((string) ($params['origin'] ?? '')));
        $destination = $this->esc(strtoupper((string) ($params['destination'] ?? '')));
        $departure = $this->esc($this->date((string) ($params['departure_date'] ?? '')));

        $body = <<<XML
    <flig:FlightInformationReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:flig="{$x['flig']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <flig:FlightInfoCriteria>
        <flig:Carrier>{$carrier}</flig:Carrier>
        <flig:FlightNumber>{$flight}</flig:FlightNumber>
        <flig:Origin>{$origin}</flig:Origin>
        <flig:Destination>{$destination}</flig:Destination>
        <flig:DepartureDate>{$departure}</flig:DepartureDate>
      </flig:FlightInfoCriteria>
    </flig:FlightInformationReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airReprice(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'repr');
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));

        $body = <<<XML
    <air:AirRepriceReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>
    </air:AirRepriceReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airCreateReservation(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'book');
        $solutionXml = (string) ($params['_air_pricing_solution_xml'] ?? '');
        if ($solutionXml === '') {
            return '';
        }

        $travelers = $this->bookingTravelersXml($params, $x);
        $fop = $this->formOfPaymentXml($params, $x);

        $body = <<<XML
    <univ:AirCreateReservationReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:univ="{$x['univ']}" xmlns:com="{$x['com']}" xmlns:air="{$x['air']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$travelers}
{$solutionXml}
{$fop}
      <com:ActionStatus Type="ACTIVE" ProviderCode="{$x['gds']}"/>
    </univ:AirCreateReservationReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function universalRecordModify(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'urmod');
        $locator = $this->esc((string) ($params['universal_locator'] ?? ''));
        $version = $this->esc((string) ($params['version'] ?? '0'));

        $body = <<<XML
    <univ:UniversalRecordModifyReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" Version="{$version}" UniversalLocatorCode="{$locator}" xmlns:univ="{$x['univ']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <univ:UniversalModifyCmd Key="mod1"/>
    </univ:UniversalRecordModifyReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airCancel(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'acan');
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));

        $body = <<<XML
    <air:AirCancelReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>
    </air:AirCancelReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airRefundQuote(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirRefundQuoteReq', $params, $schemaVer, 'rfq');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airRefund(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirRefundReq', $params, $schemaVer, 'rfd');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airVoidTicket(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'void');
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));
        $ticket = $this->esc((string) ($params['ticket_number'] ?? ''));

        $ticketXml = $ticket !== ''
            ? "\n      <air:TicketNumber>{$ticket}</air:TicketNumber>"
            : '';

        $body = <<<XML
    <air:AirVoidTicketReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>{$ticketXml}
    </air:AirVoidTicketReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airExchangeQuote(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirExchangeQuoteReq', $params, $schemaVer, 'exq');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airExchange(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirExchangeReq', $params, $schemaVer, 'exch');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airExchangeTicketing(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirExchangeTicketingReq', $params, $schemaVer, 'ext');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airMerchandising(array $params, int $schemaVer): string
    {
        $x = $this->ctx($schemaVer, 'merch');
        $solutionXml = (string) ($params['_air_pricing_solution_xml'] ?? '');

        $body = <<<XML
    <air:AirMerchandisingOfferAvailabilityReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
{$solutionXml}
    </air:AirMerchandisingOfferAvailabilityReq>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function airPrePay(array $params, int $schemaVer): string
    {
        return $this->locatorAirRequest('AirPrePayReq', $params, $schemaVer, 'prep');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function locatorAirRequest(string $reqName, array $params, int $schemaVer, string $tracePrefix): string
    {
        $x = $this->ctx($schemaVer, $tracePrefix);
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));

        $body = <<<XML
    <air:{$reqName} TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>
    </air:{$reqName}>
XML;

        return $this->envelope($body, $schemaVer);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $x
     */
    private function bookingTravelersXml(array $params, array $x): string
    {
        $passengers = $params['passengers'] ?? [];
        if (! is_array($passengers) || $passengers === []) {
            $passengers = [[
                'prefix' => 'Mr',
                'first' => 'Test',
                'last' => 'Traveler',
                'email' => 'test@example.com',
                'phone' => '5555555555',
                'dob' => '1990-01-01',
                'gender' => 'M',
                'type' => 'ADT',
            ]];
        }

        $xml = '';
        foreach ($passengers as $i => $pax) {
            if (! is_array($pax)) {
                continue;
            }
            $key = 'BT'.($i + 1);
            $prefix = $this->esc((string) ($pax['prefix'] ?? 'Mr'));
            $first = $this->esc((string) ($pax['first'] ?? ''));
            $last = $this->esc((string) ($pax['last'] ?? ''));
            $email = $this->esc((string) ($pax['email'] ?? ''));
            $phone = $this->esc((string) ($pax['phone'] ?? ''));
            $dob = $this->esc((string) ($pax['dob'] ?? ''));
            $gender = $this->esc((string) ($pax['gender'] ?? 'M'));
            $type = $this->esc((string) ($pax['type'] ?? 'ADT'));

            $dobAttr = $dob !== '' ? ' DOB="'.$dob.'"' : '';
            $xml .= <<<XML

      <com:BookingTraveler Key="{$key}" TravelerType="{$type}" Gender="{$gender}"{$dobAttr}>
        <com:BookingTravelerName Prefix="{$prefix}" First="{$first}" Last="{$last}"/>
        <com:PhoneNumber Number="{$phone}"/>
        <com:Email EmailID="{$email}"/>
      </com:BookingTraveler>
XML;
        }

        return $xml;
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $x
     */
    private function formOfPaymentXml(array $params, array $x): string
    {
        $fop = strtoupper((string) ($params['form_of_payment'] ?? 'Cash'));
        if ($fop === '' || $fop === 'CASH') {
            return "\n      <com:FormOfPayment Type=\"Cash\"/>";
        }

        return "\n      <com:FormOfPayment Type=\"".$this->esc($fop).'"/>';
    }

    public static function extractAirPricingSolutionFromPriceXml(string $xml): ?string
    {
        if ($xml === '') {
            return null;
        }

        if (! preg_match('/<(?:[\w]+:)?AirPricingSolution\b[^>]*>.*?<\/(?:[\w]+:)?AirPricingSolution>/s', $xml, $m)) {
            return null;
        }

        $block = preg_replace('/<([\w]+):/', '<air:', $m[0]);
        $block = preg_replace('/<\/([\w]+):/', '</air:', $block ?? $m[0]);
        $block = preg_replace('/ xmlns:air="[^"]*"/', '', $block ?? $m[0]);

        return '      '.($block ?? $m[0]);
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
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));

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
        $locator = $this->esc((string) ($params['air_reservation_locator'] ?? $params['universal_locator'] ?? ''));
        $ticket = $this->esc((string) ($params['ticket_number'] ?? ''));

        $ticketXml = $ticket !== ''
            ? "\n      <air:TicketNumber>{$ticket}</air:TicketNumber>"
            : '';

        $body = <<<XML
    <air:AirRetrieveDocumentReq TargetBranch="{$x['target']}" TraceId="{$x['trace']}" AuthorizedBy="UAPI" xmlns:air="{$x['air']}" xmlns:com="{$x['com']}">
      <com:BillingPointOfSaleInfo OriginApplication="{$x['origin']}"/>
      <air:AirReservationLocatorCode>{$locator}</air:AirReservationLocatorCode>{$ticketXml}
    </air:AirRetrieveDocumentReq>
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
