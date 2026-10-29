<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Shared passenger count + age-band rules for search UI and book (DOB / passport) checks.
 *
 * Adults: 12+ years · Children: 2–12 years (under 12) · Infants: under 2 years (1 day–under 2).
 * Total adults + children + infants must not exceed 9.
 */
class FlightPassengerLimits
{
    public const MAX_TOTAL = 9;

    public const ADULT_MIN_YEARS = 12;

    public const CHILD_MIN_YEARS = 2;

    public const CHILD_MAX_YEARS_EXCLUSIVE = 12;

    public const INFANT_MIN_YEARS = 0;

    public const INFANT_MAX_YEARS_EXCLUSIVE = 2;

    /**
     * @return array{adults: int, children: int, infants: int}
     */
    public static function clampCounts(int $adults, int $children, int $infants): array
    {
        $adults = max(1, min(self::MAX_TOTAL, $adults));
        $children = max(0, min(self::MAX_TOTAL, $children));
        $infants = max(0, min(self::MAX_TOTAL, $infants));

        $extra = ($adults + $children + $infants) - self::MAX_TOTAL;
        if ($extra > 0) {
            $cutInfants = min($infants, $extra);
            $infants -= $cutInfants;
            $extra -= $cutInfants;
        }
        if ($extra > 0) {
            $cutChildren = min($children, $extra);
            $children -= $cutChildren;
            $extra -= $cutChildren;
        }
        if ($extra > 0) {
            $adults = max(1, $adults - $extra);
        }

        return [
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
        ];
    }

    public static function total(int $adults, int $children, int $infants): int
    {
        return max(0, $adults) + max(0, $children) + max(0, $infants);
    }

    public static function exceedsMax(int $adults, int $children, int $infants): bool
    {
        return self::total($adults, $children, $infants) > self::MAX_TOTAL;
    }

    /**
     * Age in completed years on a reference date (journey / booking day).
     */
    public static function ageInYears(string $dob, ?CarbonInterface $on = null): ?int
    {
        try {
            $birth = Carbon::parse($dob)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $on = ($on ?? now())->copy()->startOfDay();
        if ($birth->greaterThan($on)) {
            return null;
        }

        return (int) $birth->diffInYears($on);
    }

    /**
     * @return array{min: string, max: string, label: string}|null
     */
    public static function dobBoundsForType(string $type, ?CarbonInterface $on = null): ?array
    {
        $on = ($on ?? now())->copy()->startOfDay();
        $type = strtoupper(trim($type));

        return match ($type) {
            'ADT' => [
                'min' => $on->copy()->subYears(100)->format('Y-m-d'),
                'max' => $on->copy()->subYears(self::ADULT_MIN_YEARS)->format('Y-m-d'),
                'label' => self::ADULT_MIN_YEARS.'+ years',
            ],
            'CHD', 'CNN' => [
                'min' => $on->copy()->subYears(self::CHILD_MAX_YEARS_EXCLUSIVE)->addDay()->format('Y-m-d'),
                'max' => $on->copy()->subYears(self::CHILD_MIN_YEARS)->format('Y-m-d'),
                'label' => self::CHILD_MIN_YEARS.'–'.self::CHILD_MAX_YEARS_EXCLUSIVE.' years',
            ],
            'INF' => [
                // Under 2 years: from yesterday back to the day after the 2nd birthday window.
                'min' => $on->copy()->subYears(self::INFANT_MAX_YEARS_EXCLUSIVE)->addDay()->format('Y-m-d'),
                'max' => $on->copy()->subDay()->format('Y-m-d'),
                'label' => 'under '.self::INFANT_MAX_YEARS_EXCLUSIVE.' years (1 day–under 2)',
            ],
            default => null,
        };
    }

    public static function dobMatchesType(string $type, string $dob, ?CarbonInterface $on = null): bool
    {
        $age = self::ageInYears($dob, $on);
        if ($age === null) {
            return false;
        }

        $type = strtoupper(trim($type));

        return match ($type) {
            'ADT' => $age >= self::ADULT_MIN_YEARS,
            'CHD', 'CNN' => $age >= self::CHILD_MIN_YEARS && $age < self::CHILD_MAX_YEARS_EXCLUSIVE,
            'INF' => $age >= self::INFANT_MIN_YEARS && $age < self::INFANT_MAX_YEARS_EXCLUSIVE,
            default => false,
        };
    }

    public static function ageMismatchMessage(string $type): string
    {
        $bounds = self::dobBoundsForType($type);
        $label = $bounds['label'] ?? 'the selected passenger type';

        return 'Date of birth / passport age must be '.$label.' for this passenger.';
    }
}
