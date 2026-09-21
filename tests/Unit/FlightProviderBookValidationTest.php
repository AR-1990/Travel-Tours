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
        $this->assertSame(30, $spec['name_max']);
        $this->assertSame(50, $spec['name_combined_max']);
        $this->assertArrayHasKey('passengers', $rules);
        $this->assertArrayHasKey('passengers.*', $rules);
        $this->assertContains('required', $rules['passengers.0.phone']);
        $this->assertSame('US', FlightProvider::defaultNationality(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertSame('+1', FlightProvider::defaultCountryCode(FlightProvider::DOWNTOWN_TRAVEL));

        $tooLong = \Illuminate\Support\Facades\Validator::make([
            'passengers' => [[
                'type' => 'ADT',
                'prefix' => 'Mr',
                'first' => str_repeat('A', 30),
                'last' => str_repeat('B', 30),
                'dob' => '1990-01-01',
                'gender' => 'M',
                'email' => 'a@example.com',
                'phone' => '+14057787503',
                'nationality' => 'US',
            ]],
        ], $rules);
        $this->assertTrue($tooLong->fails());
        $this->assertTrue(collect($tooLong->errors()->all())->contains(
            fn (string $m): bool => str_contains($m, '50 characters for the whole name')
        ));
    }

    public function test_hotel_book_field_specs_differ_by_provider(): void
    {
        $xc = HotelProvider::bookFieldSpecs(HotelProvider::XCONNECT);
        $dt = HotelProvider::bookFieldSpecs(HotelProvider::DOWNTOWN_TRAVEL_HOTELS);

        $this->assertSame(30, $xc['phone_max']);
        $this->assertSame(20, $dt['phone_max']);
        $this->assertTrue($dt['requires_nationality']);
        $this->assertFalse($xc['requires_nationality']);
        $this->assertArrayHasKey('first_name', HotelProvider::bookValidationRules(HotelProvider::XCONNECT));
        $this->assertArrayHasKey('nationality', HotelProvider::bookValidationRules(HotelProvider::DOWNTOWN_TRAVEL_HOTELS));
    }

    public function test_downtown_supports_full_post_book_actions(): void
    {
        $this->assertTrue(FlightProvider::supportsSeparateTicketing(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertTrue(FlightProvider::supportsStatusRefresh(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertTrue(FlightProvider::supportsRemoteCancel(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertTrue(FlightProvider::supportsVoid(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertTrue(FlightProvider::supportsRefund(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertTrue(FlightProvider::supportsSeparateTicketing(FlightProvider::TRAVELPORT));
        $this->assertTrue(FlightProvider::supportsStatusRefresh(FlightProvider::TRAVELPORT));
        $this->assertTrue(FlightProvider::supportsSeparateTicketing(FlightProvider::SUNSPRING));
        $this->assertTrue(FlightProvider::supportsStatusRefresh(FlightProvider::SUNSPRING));
        $this->assertTrue(FlightProvider::supportsRemoteCancel(FlightProvider::SUNSPRING));
        $this->assertFalse(FlightProvider::supportsVoid(FlightProvider::SUNSPRING));
        $this->assertSame('Ticket', FlightProvider::reservationStepLabel(FlightProvider::DOWNTOWN_TRAVEL));
        $this->assertSame('Ticket', FlightProvider::reservationStepLabel(FlightProvider::SUNSPRING));
    }
}
