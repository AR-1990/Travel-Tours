<?php

namespace Tests\Unit;

use App\Support\FlightProvider;
use App\Support\HotelProvider;
use Tests\TestCase;

class FlightProviderBookValidationTest extends TestCase
{
    public function test_travelport_uses_flat_passenger_fields(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::TRAVELPORT, 1);
        $spec = FlightProvider::bookFieldSpecs(FlightProvider::TRAVELPORT);

        $this->assertArrayHasKey('passenger_first', $rules);
        $this->assertArrayNotHasKey('passengers', $rules);
        $this->assertFalse(FlightProvider::usesPassengerArray(FlightProvider::TRAVELPORT));
        $this->assertFalse($spec['show_docs']);
        $this->assertFalse($spec['docs_required']);
    }

    public function test_sunspring_requires_passengers_and_travel_documents(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::SUNSPRING, 2);
        $spec = FlightProvider::bookFieldSpecs(FlightProvider::SUNSPRING);

        $this->assertTrue(FlightProvider::usesPassengerArray(FlightProvider::SUNSPRING));
        $this->assertTrue($spec['docs_required']);
        $this->assertSame(3, $spec['nationality_max']);
        $this->assertSame(['required', 'array', 'min:2', 'max:2'], $rules['passengers']);
        $this->assertContains('required', $rules['passengers.*.national_id']);
        $this->assertContains('required', $rules['passengers.*.passport_number']);
        $this->assertContains('required', $rules['passengers.0.email']);
        $this->assertSame('IRN', FlightProvider::defaultNationality(FlightProvider::SUNSPRING));
    }

    public function test_downtown_requires_passengers_without_travel_documents(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::DOWNTOWN_TRAVEL, 1);
        $spec = FlightProvider::bookFieldSpecs(FlightProvider::DOWNTOWN_TRAVEL);

        $this->assertTrue(FlightProvider::usesPassengerArray(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertFalse($spec['docs_required']);
        $this->assertTrue($spec['passport_optional']);
        $this->assertSame(2, $spec['nationality_max']);
        $this->assertArrayHasKey('passengers', $rules);
        $this->assertContains('required', $rules['passengers.0.phone']);
        $this->assertSame('US', FlightProvider::defaultNationality(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertSame('+1', FlightProvider::defaultCountryCode(FlightProvider::DOWNTOWN_TRAVEL));
    }

    public function test_hotel_book_field_specs_differ_by_provider(): void
    {
        $xc = HotelProvider::bookFieldSpecs(HotelProvider::XCONNECT);
        $dt = HotelProvider::bookFieldSpecs(HotelProvider::DOWNTOWN_TRAVEL_HOTELS);

        $this->assertSame(30, $xc['phone_max']);
        $this->assertSame(20, $dt['phone_max']);
        $this->assertArrayHasKey('first_name', HotelProvider::bookValidationRules(HotelProvider::XCONNECT));
    }
}
