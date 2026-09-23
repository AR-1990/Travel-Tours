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
        $this->assertSame(10, $spec['national_id_min']);
        $this->assertSame(10, $spec['national_id_max']);
        $this->assertSame(['required', 'array', 'min:2', 'max:2'], $rules['passengers']);
        $this->assertContains('nullable', $rules['passengers.*.national_id']);
        $this->assertContains('required', $rules['passengers.*.passport_number']);
        $this->assertContains('required', $rules['passengers.0.email']);
        $this->assertSame('IRN', FlightProvider::defaultNationality(FlightProvider::SUNSPRING));
    }

    public function test_sunspring_national_id_requires_iranian_checksum(): void
    {
        $this->assertTrue(FlightProvider::isValidIranianNationalId('0013542419'));
        $this->assertTrue(FlightProvider::isValidIranianNationalId('5001000009'));
        $this->assertFalse(FlightProvider::isValidIranianNationalId('0000000000'));
        $this->assertFalse(FlightProvider::isValidIranianNationalId('1234567890'));
        $this->assertFalse(FlightProvider::isValidIranianNationalId('12345678'));

        $rules = FlightProvider::bookValidationRules(FlightProvider::SUNSPRING, 1);
        $base = [
            'type' => 'ADT',
            'prefix' => 'Mr',
            'first' => 'Ali',
            'last' => 'Ahmadi',
            'dob' => '1990-01-01',
            'gender' => 'M',
            'email' => 'ali@example.com',
            'phone' => '9151112233',
            'passport_number' => 'A12345678',
            'passport_expire' => '2030-12-31',
        ];

        $invalidIranian = \Illuminate\Support\Facades\Validator::make([
            'country_code' => '+98',
            'passengers' => [array_merge($base, [
                'nationality' => 'IRN',
                'national_id' => '1234567890',
            ])],
        ], $rules);
        $this->assertTrue($invalidIranian->fails());
        $this->assertTrue(collect($invalidIranian->errors()->all())->contains(
            fn (string $m): bool => str_contains($m, 'Iranian national ID')
        ));

        $missingIranianId = \Illuminate\Support\Facades\Validator::make([
            'country_code' => '+98',
            'passengers' => [array_merge($base, [
                'nationality' => 'IRN',
                'national_id' => '',
            ])],
        ], $rules);
        $this->assertTrue($missingIranianId->fails());

        $validIranian = \Illuminate\Support\Facades\Validator::make([
            'country_code' => '+98',
            'passengers' => [array_merge($base, [
                'nationality' => 'IRN',
                'national_id' => '0013542419',
            ])],
        ], $rules);
        $this->assertFalse($validIranian->fails(), implode('; ', $validIranian->errors()->all()));

        $pakistaniWithoutNationalId = \Illuminate\Support\Facades\Validator::make([
            'country_code' => '+92',
            'passengers' => [array_merge($base, [
                'nationality' => 'PAK',
                'national_id' => '',
                'phone' => '3001234567',
            ])],
        ], $rules);
        $this->assertFalse($pakistaniWithoutNationalId->fails(), implode('; ', $pakistaniWithoutNationalId->errors()->all()));
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
