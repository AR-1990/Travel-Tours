<?php

namespace Tests\Unit;

use App\Support\TravelportUserMessage;
use PHPUnit\Framework\TestCase;

class TravelportUserMessageTest extends TestCase
{
    public function test_maps_segment_not_bookable_to_friendly_message(): void
    {
        $body = <<<'XML'
<air:AvailabilityErrorInfo>
<air:AirSegmentError>
<air:AirSegment Carrier="RO" FlightNumber="382" Origin="CDG" Destination="OTP"/>
<air:ErrorMessage>*0 AVAIL/WL CLOSED*</air:ErrorMessage>
</air:AirSegmentError>
</air:AvailabilityErrorInfo>
XML;

        $message = TravelportUserMessage::from(
            'air_create_reservation',
            'One or more segments are not bookable. (v52)',
            $body
        );

        $this->assertStringContainsString('RO 382', $message);
        $this->assertStringContainsString('no seats', $message);
        $this->assertStringNotContainsString('v52', $message);
    }

    public function test_maps_record_locator_not_found(): void
    {
        $message = TravelportUserMessage::from(
            'air_ticketing',
            'Record locator not found. (v52)',
            ''
        );

        $this->assertStringContainsString('booking reference', $message);
    }
}
