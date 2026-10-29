<?php

namespace Tests\Unit;

use App\Services\Travelport\TravelportFlightParser;
use Tests\TestCase;

class TravelportFlightParserPriceTest extends TestCase
{
    public function test_low_fare_search_prefers_approximate_total_price(): void
    {
        $xml = <<<'XML'
<air:LowFareSearchRsp xmlns:air="http://www.travelport.com/schema/air_v52_0" TraceId="t-price">
  <air:AirSegmentList>
    <air:AirSegment Key="S1" Carrier="QR" FlightNumber="611" Origin="KHI" Destination="DOH"
      DepartureTime="2026-11-10T09:30:00.000+05:00" ArrivalTime="2026-11-10T10:00:00.000+03:00"/>
  </air:AirSegmentList>
  <air:AirPricingSolution Key="P1"
    TotalPrice="KWD1200.000" BasePrice="KWD900.000" Taxes="KWD300.000"
    ApproximateTotalPrice="USD3820.77" ApproximateBasePrice="USD2862.00" ApproximateTaxes="USD958.77">
    <air:Journey>
      <air:AirSegmentRef Key="S1"/>
    </air:Journey>
    <air:AirSegmentRef Key="S1"/>
    <air:AirPricingInfo PlatingCarrier="QR">
      <air:BookingInfo BookingCode="O" CabinClass="Economy" SegmentRef="S1"/>
    </air:AirPricingInfo>
  </air:AirPricingSolution>
</air:LowFareSearchRsp>
XML;

        $parsed = (new TravelportFlightParser)->parseLowFareSearch($xml);
        $this->assertNotEmpty($parsed['solutions']);
        $this->assertSame('USD3820.77', $parsed['solutions'][0]['total_price']);
        $this->assertSame('USD2862.00', $parsed['solutions'][0]['base_price']);
        $this->assertSame('USD958.77', $parsed['solutions'][0]['taxes']);
    }
}
