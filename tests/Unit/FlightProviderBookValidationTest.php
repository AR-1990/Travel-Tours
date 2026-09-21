<?php

namespace Tests\Unit;

use App\Support\FlightProvider;
use Tests\TestCase;

class FlightProviderBookValidationTest extends TestCase
{
    public function test_travelport_uses_flat_passenger_fields(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::TRAVELPORT, 1);

        $this->assertArrayHasKey('passenger_first', $rules);
        $this->assertArrayNotHasKey('passengers', $rules);
        $this->assertFalse(FlightProvider::usesPassengerArray(FlightProvider::TRAVELPORT));
        $this->assertFalse(FlightProvider::requiresTravelDocuments(FlightProvider::TRAVELPORT));
    }

    public function test_sunspring_requires_passengers_and_travel_documents(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::SUNSPRING, 2);

        $this->assertTrue(FlightProvider::usesPassengerArray(FlightProvider::SUNSPRING));
        $this->assertTrue(FlightProvider::requiresTravelDocuments(FlightProvider::SUNSPRING));
        $this->assertSame(['required', 'array', 'min:2', 'max:2'], $rules['passengers']);
        $this->assertContains('required', $rules['passengers.*.national_id']);
        $this->assertContains('required', $rules['passengers.*.passport_number']);
        $this->assertContains('required', $rules['passengers.0.email']);
        $this->assertSame('IRN', FlightProvider::defaultNationality(FlightProvider::SUNSPRING));
    }

    public function test_downtown_requires_passengers_without_travel_documents(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::DOWNTOWN_TRAVEL, 1);

        $this->assertTrue(FlightProvider::usesPassengerArray(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertFalse(FlightProvider::requiresTravelDocuments(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertArrayHasKey('passengers', $rules);
        $this->assertContains('nullable', $rules['passengers.*.passport_number']);
        $this->assertContains('required', $rules['passengers.0.phone']);
        $this->assertSame('US', FlightProvider::defaultNationality(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertSame('+1', FlightProvider::defaultCountryCode(FlightProvider::DOWNTOWN_TRAVEL));
    }
}
