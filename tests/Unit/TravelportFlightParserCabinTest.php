<?php

namespace Tests\Unit;

use App\Services\Travelport\TravelportFlightParser;
use Tests\TestCase;

class TravelportFlightParserCabinTest extends TestCase
{
    public function test_low_fare_search_attaches_cabin_and_booking_class_from_booking_info(): void
    {
        $xml = <<<'XML'
<air:LowFareSearchRsp xmlns:air="http://www.travelport.com/schema/air_v52_0" TraceId="t1">
  <air:AirSegmentList>
    <air:AirSegment Key="seg1" Carrier="EK" FlightNumber="319" Origin="NRT" Destination="DXB"
      DepartureTime="2026-12-14T22:20:00.000+09:00" ArrivalTime="2026-12-15T05:30:00.000+04:00"/>
    <air:AirSegment Key="seg2" Carrier="EK" FlightNumber="622" Origin="DXB" Destination="LHE"
      DepartureTime="2026-12-15T16:05:00.000+04:00" ArrivalTime="2026-12-15T19:55:00.000+05:00"/>
  </air:AirSegmentList>
  <air:AirPricingSolution Key="sol1" TotalPrice="USD900.00" BasePrice="USD700.00" Taxes="USD200.00">
    <air:Journey TravelTime="P0DT12H10M0S">
      <air:AirSegmentRef Key="seg1"/>
      <air:AirSegmentRef Key="seg2"/>
    </air:Journey>
    <air:AirSegmentRef Key="seg1"/>
    <air:AirSegmentRef Key="seg2"/>
    <air:AirPricingInfo PlatingCarrier="EK">
      <air:BookingInfo BookingCode="T" CabinClass="Economy" SegmentRef="seg1"/>
      <air:BookingInfo BookingCode="T" CabinClass="Economy" SegmentRef="seg2"/>
    </air:AirPricingInfo>
  </air:AirPricingSolution>
</air:LowFareSearchRsp>
XML;

        $parsed = (new TravelportFlightParser)->parseLowFareSearch($xml);

        $this->assertCount(1, $parsed['solutions']);
        $sol = $parsed['solutions'][0];
        $this->assertSame('Economy', $sol['cabin']);
        $this->assertSame('Economy', $sol['segments'][0]['cabin']);
        $this->assertSame('T', $sol['segments'][0]['booking_class']);
        $this->assertSame('T', $sol['segments'][0]['class_of_service']);
        $this->assertSame('Economy', $sol['journeys'][0]['segments'][1]['cabin']);
        $this->assertSame('T', $sol['journeys'][0]['segments'][1]['class_of_service']);
        $this->assertSame('P0DT12H10M0S', $sol['journeys'][0]['travel_time']);
    }
}
