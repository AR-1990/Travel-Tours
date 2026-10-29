<?php

namespace Tests\Unit;

use App\Support\FlightPassengerLimits;
use App\Support\FlightProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FlightPassengerLimitsTest extends TestCase
{
    public function test_clamp_counts_locks_total_at_nine(): void
    {
        $clamped = FlightPassengerLimits::clampCounts(7, 3, 2);
        $this->assertSame(9, FlightPassengerLimits::total(
            $clamped['adults'],
            $clamped['children'],
            $clamped['infants']
        ));
        $this->assertSame(7, $clamped['adults']);
        $this->assertLessThanOrEqual(2, $clamped['children'] + $clamped['infants']);
    }

    public function test_age_bands_match_adult_child_infant(): void
    {
        $on = Carbon::parse('2026-10-29');

        $this->assertTrue(FlightPassengerLimits::dobMatchesType('ADT', '2010-10-29', $on)); // 16
        $this->assertFalse(FlightPassengerLimits::dobMatchesType('ADT', '2015-10-30', $on)); // under 12

        $this->assertTrue(FlightPassengerLimits::dobMatchesType('CHD', '2018-10-29', $on)); // 8
        $this->assertTrue(FlightPassengerLimits::dobMatchesType('CHD', '2024-10-29', $on)); // 2
        $this->assertFalse(FlightPassengerLimits::dobMatchesType('CHD', '2014-10-29', $on)); // 12 → adult
        $this->assertFalse(FlightPassengerLimits::dobMatchesType('CHD', '2025-10-29', $on)); // 1 → infant

        $this->assertTrue(FlightPassengerLimits::dobMatchesType('INF', '2025-10-29', $on)); // 1
        $this->assertTrue(FlightPassengerLimits::dobMatchesType('INF', '2026-01-15', $on)); // under 1
        $this->assertFalse(FlightPassengerLimits::dobMatchesType('INF', '2024-10-29', $on)); // 2 → child
    }

    public function test_book_validation_rejects_wrong_dob_for_passenger_type(): void
    {
        $rules = FlightProvider::bookValidationRules(FlightProvider::TRAVELPORT, 1);

        $childAsAdult = Validator::make([
            'passengers' => [[
                'type' => 'ADT',
                'prefix' => 'Mr',
                'first' => 'Sam',
                'last' => 'Kid',
                'dob' => now()->subYears(8)->format('Y-m-d'),
                'gender' => 'M',
                'email' => 'a@example.com',
                'phone' => '+14055550100',
            ]],
        ], $rules);
        $this->assertTrue($childAsAdult->fails());
        $this->assertTrue(collect($childAsAdult->errors()->all())->contains(
            fn (string $m): bool => str_contains($m, '12+ years')
        ));

        $okInfant = Validator::make([
            'passengers' => [[
                'type' => 'INF',
                'prefix' => 'Mstr',
                'first' => 'Baby',
                'last' => 'Kid',
                'dob' => now()->subYear()->format('Y-m-d'),
                'gender' => 'M',
                'email' => 'a@example.com',
                'phone' => '+14055550100',
            ]],
        ], $rules);
        $this->assertFalse($okInfant->fails(), implode('; ', $okInfant->errors()->all()));
    }
}
