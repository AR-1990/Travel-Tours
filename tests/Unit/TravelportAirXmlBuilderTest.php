<?php

namespace Tests\Unit;

use App\Services\Travelport\TravelportAirCatalog;
use App\Services\Travelport\TravelportAirXmlBuilder;
use Tests\TestCase;

class TravelportAirXmlBuilderTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function operationKeysProvider(): array
    {
        return [
            'low_fare_search' => ['low_fare_search'],
            'low_fare_search_async' => ['low_fare_search_async'],
            'availability_search' => ['availability_search'],
            'air_fare_display' => ['air_fare_display'],
            'flight_time_table' => ['flight_time_table'],
            'flight_details' => ['flight_details'],
            'flight_information' => ['flight_information'],
            'air_price' => ['air_price'],
            'air_fare_rules' => ['air_fare_rules'],
            'seat_map' => ['seat_map'],
            'air_reprice' => ['air_reprice'],
            'air_create_reservation' => ['air_create_reservation'],
            'universal_record_retrieve' => ['universal_record_retrieve'],
            'universal_record_cancel' => ['universal_record_cancel'],
            'universal_record_modify' => ['universal_record_modify'],
            'air_ticketing' => ['air_ticketing'],
            'air_retrieve_document' => ['air_retrieve_document'],
            'air_void_ticket' => ['air_void_ticket'],
            'air_refund_quote' => ['air_refund_quote'],
            'air_refund' => ['air_refund'],
            'air_cancel' => ['air_cancel'],
            'air_exchange_quote' => ['air_exchange_quote'],
            'air_exchange' => ['air_exchange'],
            'air_exchange_ticketing' => ['air_exchange_ticketing'],
            'air_merchandising' => ['air_merchandising'],
            'air_pre_pay' => ['air_pre_pay'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('operationKeysProvider')]
    public function test_all_catalog_operations_build_xml(string $operation): void
    {
        $builder = new TravelportAirXmlBuilder;
        $params = $this->sampleParams($operation);
        $xml = $builder->build($operation, $params, 52);

        if ($operation === 'air_create_reservation' && empty($params['_air_pricing_solution_xml'])) {
            $this->assertSame('', $xml);

            return;
        }

        $this->assertNotEmpty($xml, "No XML for: {$operation}");
        $this->assertStringContainsString('soapenv:Envelope', $xml);
    }

    public function test_extract_air_pricing_solution_from_price_xml(): void
    {
        $sample = <<<'XML'
<air:AirPriceRsp>
  <air:AirPricingSolution Key="s1" TotalPrice="GBP100">
    <air:AirSegmentRef Key="seg1"/>
  </air:AirPricingSolution>
</air:AirPriceRsp>
XML;

        $extracted = TravelportAirXmlBuilder::extractAirPricingSolutionFromPriceXml($sample);
        $this->assertNotNull($extracted);
        $this->assertStringContainsString('AirPricingSolution', $extracted);
    }

    public function test_prepare_booking_pricing_solution_injects_segments_and_host_token(): void
    {
        $priceXml = <<<'XML'
<air:AirPriceRsp xmlns:air="http://www.travelport.com/schema/air_v52_0">
  <air:AirItinerary>
    <air:AirSegment Key="seg1" Carrier="B6" FlightNumber="100" Origin="JFK" Destination="LAX"/>
  </air:AirItinerary>
  <air:AirPricingSolution Key="s1" TotalPrice="USD100">
    <air:AirSegmentRef Key="seg1"/>
    <air:AirPricingInfo Key="pi1">
      <air:PassengerType Code="ADT" BookingTravelerRef="BT1"/>
    </air:AirPricingInfo>
  </air:AirPricingSolution>
  <air:HostToken Key="ht1">TOKENVALUE</air:HostToken>
</air:AirPriceRsp>
XML;

        $prepared = TravelportAirXmlBuilder::prepareAirPricingSolutionForBooking($priceXml);
        $this->assertNotNull($prepared);
        $this->assertStringContainsString('AirSegment', $prepared);
        $this->assertStringContainsString('HostToken', $prepared);
        $this->assertStringContainsString('BookingTravelerRef="1"', $prepared);
        $this->assertStringNotContainsString('common_v52_0:', $prepared);
        $this->assertStringNotContainsString('AirSegmentRef', $prepared);
    }

    public function test_normalize_embedded_travelport_xml_rewrites_versioned_prefixes(): void
    {
        $normalized = TravelportAirXmlBuilder::normalizeEmbeddedTravelportXml(
            '<common_v52_0:HostToken Key="x">abc</common_v52_0:HostToken>'
        );
        $this->assertStringContainsString('<com:HostToken', $normalized);
        $this->assertStringNotContainsString('common_v52_0:', $normalized);
    }

    public function test_air_create_reservation_places_form_of_payment_before_pricing_solution(): void
    {
        $builder = new TravelportAirXmlBuilder;
        $xml = $builder->build('air_create_reservation', [
            '_air_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1" TotalPrice="GBP100"/>',
            'passengers' => [[
                'prefix' => 'Mr', 'first' => 'John', 'last' => 'Smith',
                'email' => 'john@example.com', 'phone' => '5551234567',
                'dob' => '1990-05-01', 'gender' => 'M', 'type' => 'ADT',
            ]],
            'form_of_payment' => 'Cash',
        ], 52);

        $this->assertLessThan(
            strpos($xml, 'AirPricingSolution'),
            strpos($xml, 'FormOfPayment'),
            'FormOfPayment should appear before AirPricingSolution'
        );
    }

    public function test_air_create_reservation_includes_traveler_and_pricing(): void
    {
        $builder = new TravelportAirXmlBuilder;
        $xml = $builder->build('air_create_reservation', [
            '_air_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1" TotalPrice="GBP100"/>',
            'passengers' => [[
                'prefix' => 'Mr',
                'first' => 'John',
                'last' => 'Smith',
                'email' => 'john@example.com',
                'phone' => '5551234567',
                'dob' => '1990-05-01',
                'gender' => 'M',
                'type' => 'ADT',
            ]],
            'form_of_payment' => 'Cash',
        ], 52);

        $this->assertStringContainsString('AirCreateReservationReq', $xml);
        $this->assertStringContainsString('BookingTraveler', $xml);
        $this->assertStringContainsString('John', $xml);
        $this->assertStringContainsString('AirPricingSolution', $xml);
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleParams(string $operation): array
    {
        $base = [
            'origin' => 'LHR',
            'destination' => 'JFK',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(37)->format('Y-m-d'),
            'adults' => 1,
            'fare_basis' => 'Y26',
            'carrier' => 'BA',
            'flight_number' => '117',
            'departure_time' => now()->addDays(30)->format('Y-m-d').'T10:00:00',
            'universal_locator' => 'TEST12',
            'air_reservation_locator' => 'AIR123',
            'version' => '0',
            'ticket_number' => '1234567890',
            '_air_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1" TotalPrice="GBP100"/>',
            '_pricing_solution_xml' => '      <air:AirPricingSolution Key="s1"/>',
            '_lfs_xml' => '<air:LowFareSearchRsp xmlns:air="http://www.travelport.com/schema/air_v52_0"><air:AirPricingSolution Key="s1"><air:AirSegmentRef Key="seg1"/></air:AirPricingSolution><air:AirSegment Key="seg1" Carrier="BA" FlightNumber="117" Origin="LHR" Destination="JFK" DepartureTime="2026-08-01T10:00:00"/></air:LowFareSearchRsp>',
        ];

        if ($operation === 'air_create_reservation') {
            $base['passengers'] = [[
                'prefix' => 'Mr', 'first' => 'Test', 'last' => 'User',
                'email' => 't@example.com', 'phone' => '555', 'dob' => '1990-01-01', 'gender' => 'M', 'type' => 'ADT',
            ]];
        }

        return $base;
    }
}
