<?php

namespace App\Support;

use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use App\Services\Travelport\TravelportIntegrationConfig;

class FlightProvider
{
    public const TRAVELPORT = 'travelport';

    public const SUNSPRING = 'sunspring';

    public const DOWNTOWN_TRAVEL = 'downtown_travel';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::TRAVELPORT, self::SUNSPRING, self::DOWNTOWN_TRAVEL];
    }

    public static function current(): string
    {
        $fromSession = (string) session('flight.provider', '');
        if (in_array($fromSession, self::all(), true)) {
            return $fromSession;
        }

        if (DowntownTravelIntegrationConfig::isReadyForAir()
            && ! TravelportIntegrationConfig::isReadyForAir()
            && ! SunSpringIntegrationConfig::isReadyForAir()) {
            return self::DOWNTOWN_TRAVEL;
        }

        if (SunSpringIntegrationConfig::isReadyForAir() && ! TravelportIntegrationConfig::isReadyForAir()) {
            return self::SUNSPRING;
        }

        return self::TRAVELPORT;
    }

    public static function set(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, self::all(), true)) {
            $provider = self::TRAVELPORT;
        }
        session(['flight.provider' => $provider]);
    }

    public static function isSunSpring(): bool
    {
        return self::current() === self::SUNSPRING;
    }

    public static function isDowntownTravel(): bool
    {
        return self::current() === self::DOWNTOWN_TRAVEL;
    }

    /**
     * SunSpring + Downtown book APIs expect passengers[] rows (not Travelport flat fields).
     */
    public static function usesPassengerArray(?string $provider = null): bool
    {
        $provider = strtolower((string) ($provider ?? self::current()));

        return in_array($provider, [self::SUNSPRING, self::DOWNTOWN_TRAVEL], true);
    }

    /**
     * SunSpring requires national ID + passport on every traveler.
     */
    public static function requiresTravelDocuments(?string $provider = null): bool
    {
        return strtolower((string) ($provider ?? self::current())) === self::SUNSPRING;
    }

    public static function defaultNationality(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::SUNSPRING => 'IRN',
            self::DOWNTOWN_TRAVEL => 'US',
            default => 'US',
        };
    }

    /**
     * ISO 3166-1 alpha-2 codes accepted for Downtown Travel nationality.
     *
     * @return list<string>
     */
    public static function isoAlpha2Nationalities(): array
    {
        return [
            'AE', 'AF', 'AL', 'AM', 'AR', 'AT', 'AU', 'AZ', 'BA', 'BD', 'BE', 'BG', 'BH', 'BR', 'BY',
            'CA', 'CH', 'CL', 'CN', 'CO', 'CY', 'CZ', 'DE', 'DK', 'DZ', 'EE', 'EG', 'ES', 'ET', 'FI',
            'FR', 'GB', 'GE', 'GH', 'GR', 'HK', 'HR', 'HU', 'ID', 'IE', 'IL', 'IN', 'IQ', 'IR', 'IS',
            'IT', 'JO', 'JP', 'KE', 'KG', 'KR', 'KW', 'KZ', 'LB', 'LK', 'LT', 'LU', 'LV', 'LY', 'MA',
            'MD', 'MK', 'MT', 'MX', 'MY', 'NG', 'NL', 'NO', 'NP', 'NZ', 'OM', 'PH', 'PK', 'PL', 'PT',
            'QA', 'RO', 'RS', 'RU', 'SA', 'SD', 'SE', 'SG', 'SI', 'SK', 'SO', 'SY', 'TH', 'TJ', 'TM',
            'TN', 'TR', 'TZ', 'UA', 'UG', 'US', 'UZ', 'VN', 'YE', 'ZA', 'ZW',
        ];
    }

    /**
     * Nationality options for book forms (code => label).
     *
     * @return array<string, string>
     */
    public static function nationalityOptions(?string $provider = null): array
    {
        $provider = strtolower((string) ($provider ?? self::current()));
        if ($provider === self::SUNSPRING) {
            return [
                'IRN' => 'Iran (IRN)',
                'USA' => 'United States (USA)',
                'ARE' => 'UAE (ARE)',
                'GBR' => 'United Kingdom (GBR)',
                'PAK' => 'Pakistan (PAK)',
                'IND' => 'India (IND)',
                'SAU' => 'Saudi Arabia (SAU)',
                'QAT' => 'Qatar (QAT)',
                'TUR' => 'Turkey (TUR)',
            ];
        }

        $labels = [
            'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AE' => 'United Arab Emirates',
            'PK' => 'Pakistan', 'IN' => 'India', 'SA' => 'Saudi Arabia', 'QA' => 'Qatar', 'EG' => 'Egypt',
            'TR' => 'Turkey', 'IR' => 'Iran', 'IQ' => 'Iraq', 'JO' => 'Jordan', 'KW' => 'Kuwait',
            'BH' => 'Bahrain', 'OM' => 'Oman', 'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France',
            'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands', 'CN' => 'China', 'JP' => 'Japan',
            'KR' => 'South Korea', 'PH' => 'Philippines', 'NG' => 'Nigeria', 'ZA' => 'South Africa',
            'BR' => 'Brazil', 'MX' => 'Mexico',
        ];

        $out = [];
        foreach (self::isoAlpha2Nationalities() as $code) {
            $out[$code] = ($labels[$code] ?? $code).' ('.$code.')';
        }

        return $out;
    }

    public static function defaultCountryCode(?string $provider = null): string
    {
        return self::requiresTravelDocuments($provider) ? '+98' : '+1';
    }

    /**
     * HTML + validation constraints for book-form fields, keyed by provider.
     *
     * @return array{
     *   hint: string,
     *   name_pattern: string,
     *   name_title: string,
     *   name_min: int,
     *   name_max: int,
     *   phone_pattern: string,
     *   phone_title: string,
     *   phone_min: int,
     *   phone_max: int,
     *   nationality_pattern: string,
     *   nationality_title: string,
     *   nationality_max: int,
     *   nationality_min: int,
     *   nationality_placeholder: string,
     *   country_code_pattern: string,
     *   country_code_title: string,
     *   country_code_max: int,
     *   national_id_pattern: string|null,
     *   national_id_title: string|null,
     *   national_id_min: int|null,
     *   national_id_max: int|null,
     *   passport_pattern: string,
     *   passport_title: string,
     *   passport_min: int,
     *   passport_max: int,
     *   passport_optional: bool,
     *   show_docs: bool,
     *   docs_required: bool,
     *   show_country_code: bool,
     *   show_nationality: bool
     * }
     */
    public static function bookFieldSpecs(?string $provider = null): array
    {
        $provider = strtolower((string) ($provider ?? self::current()));
        if (! in_array($provider, self::all(), true)) {
            $provider = self::TRAVELPORT;
        }

        $namePattern = '[A-Za-z][A-Za-z \\-\']{0,78}[A-Za-z]?';
        $nameTitle = 'Letters, spaces, hyphen or apostrophe only';

        return match ($provider) {
            self::SUNSPRING => [
                'hint' => 'Enter every traveler from your search. SunSpring needs a valid national ID and passport (e.g. A12345678).',
                'name_pattern' => $namePattern,
                'name_title' => $nameTitle,
                'name_min' => 2,
                'name_max' => 80,
                'phone_pattern' => '[0-9+() \\-]{7,20}',
                'phone_title' => 'Phone digits only (7–20 characters)',
                'phone_min' => 7,
                'phone_max' => 20,
                'nationality_pattern' => '[A-Za-z]{3}',
                'nationality_title' => '3-letter country code (e.g. IRN)',
                'nationality_max' => 3,
                'nationality_min' => 3,
                'nationality_placeholder' => 'IRN',
                'country_code_pattern' => '\\+[0-9]{1,4}',
                'country_code_title' => 'Dialing code like +98',
                'country_code_max' => 5,
                'national_id_pattern' => '[0-9]{8,12}',
                'national_id_title' => '8–12 digit national ID',
                'national_id_min' => 8,
                'national_id_max' => 12,
                'passport_pattern' => '[A-Za-z0-9]{5,15}',
                'passport_title' => 'Passport: 5–15 letters/numbers',
                'passport_min' => 5,
                'passport_max' => 15,
                'passport_optional' => false,
                'show_docs' => true,
                'docs_required' => true,
                'show_country_code' => true,
                'show_nationality' => true,
            ],
            self::DOWNTOWN_TRAVEL => [
                'hint' => 'Enter every traveler from your search. Pick a real nationality from the list and use a phone with country code. Passport only if the fare requires it.',
                'name_pattern' => $namePattern,
                'name_title' => $nameTitle,
                'name_min' => 2,
                'name_max' => 80,
                'phone_pattern' => '\\+?[0-9() \\-]{7,20}',
                'phone_title' => 'Phone with country code digits (e.g. +14057787503)',
                'phone_min' => 7,
                'phone_max' => 20,
                'nationality_pattern' => '[A-Za-z]{2}',
                'nationality_title' => '2-letter country code (e.g. US)',
                'nationality_max' => 2,
                'nationality_min' => 2,
                'nationality_placeholder' => 'US',
                'country_code_pattern' => '\\+[0-9]{1,4}',
                'country_code_title' => 'Dialing code like +1',
                'country_code_max' => 5,
                'national_id_pattern' => null,
                'national_id_title' => null,
                'national_id_min' => null,
                'national_id_max' => null,
                'passport_pattern' => '[A-Za-z0-9]{5,15}',
                'passport_title' => 'Passport: 5–15 letters/numbers (optional)',
                'passport_min' => 5,
                'passport_max' => 15,
                'passport_optional' => true,
                'show_docs' => true,
                'docs_required' => false,
                'show_country_code' => true,
                'show_nationality' => true,
            ],
            default => [
                'hint' => 'Enter the lead traveler details for this Travelport booking.',
                'name_pattern' => $namePattern,
                'name_title' => $nameTitle,
                'name_min' => 2,
                'name_max' => 80,
                'phone_pattern' => '[0-9+() \\-]{7,30}',
                'phone_title' => 'Phone digits (7–30 characters)',
                'phone_min' => 7,
                'phone_max' => 30,
                'nationality_pattern' => '[A-Za-z]{2,3}',
                'nationality_title' => '2–3 letter country code',
                'nationality_max' => 3,
                'nationality_min' => 2,
                'nationality_placeholder' => 'US',
                'country_code_pattern' => '\\+[0-9]{1,4}',
                'country_code_title' => 'Dialing code like +1',
                'country_code_max' => 5,
                'national_id_pattern' => null,
                'national_id_title' => null,
                'national_id_min' => null,
                'national_id_max' => null,
                'passport_pattern' => '[A-Za-z0-9]{5,15}',
                'passport_title' => 'Passport number',
                'passport_min' => 5,
                'passport_max' => 15,
                'passport_optional' => true,
                'show_docs' => false,
                'docs_required' => false,
                'show_country_code' => false,
                'show_nationality' => false,
            ],
        };
    }

    /**
     * Validation rules for the public/admin book form for a given provider.
     *
     * @return array<string, list<string|\Illuminate\Validation\Rules\Regex>|string>
     */
    public static function bookValidationRules(string $provider, int $expectedPassengers = 1): array
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, self::all(), true)) {
            $provider = self::TRAVELPORT;
        }

        $expected = max(1, $expectedPassengers);
        $spec = self::bookFieldSpecs($provider);
        $nameRegex = '/^[A-Za-z][A-Za-z \\-\']{0,78}$/';
        $phoneRegex = '/^[0-9+() \\-]{'.$spec['phone_min'].','.$spec['phone_max'].'}$/';
        $countryRegex = '/^\\+[0-9]{1,4}$/';
        $nationalityRegex = '/^[A-Za-z]{'.$spec['nationality_min'].','.$spec['nationality_max'].'}$/';

        if (! self::usesPassengerArray($provider)) {
            return [
                'provider' => ['nullable', 'in:'.implode(',', self::all())],
                'passenger_first' => ['required', 'string', 'min:'.$spec['name_min'], 'max:'.$spec['name_max'], 'regex:'.$nameRegex],
                'passenger_last' => ['required', 'string', 'min:'.$spec['name_min'], 'max:'.$spec['name_max'], 'regex:'.$nameRegex],
                'passenger_email' => ['required', 'email', 'max:120'],
                'passenger_phone' => ['required', 'string', 'min:'.$spec['phone_min'], 'max:'.$spec['phone_max'], 'regex:'.$phoneRegex],
                'passenger_dob' => ['required', 'date', 'before:today'],
                'passenger_gender' => ['required', 'in:M,F'],
                'passenger_prefix' => ['nullable', 'string', 'max:10'],
                'form_of_payment' => ['nullable', 'in:Cash,Credit,Check'],
            ];
        }

        $rules = [
            'provider' => ['nullable', 'in:'.implode(',', self::all())],
            'passengers' => ['required', 'array', 'min:'.$expected, 'max:'.$expected],
            'passengers.*.type' => ['required', 'in:ADT,CHD,INF'],
            'passengers.*.first' => ['required', 'string', 'min:'.$spec['name_min'], 'max:'.$spec['name_max'], 'regex:'.$nameRegex],
            'passengers.*.last' => ['required', 'string', 'min:'.$spec['name_min'], 'max:'.$spec['name_max'], 'regex:'.$nameRegex],
            'passengers.*.dob' => ['required', 'date', 'before:today'],
            'passengers.*.gender' => ['required', 'in:M,F'],
            'passengers.*.prefix' => ['nullable', 'in:Mr,Mrs,Ms,Miss,Mstr'],
            'passengers.*.email' => ['nullable', 'email', 'max:120'],
            'passengers.*.phone' => ['nullable', 'string', 'min:'.$spec['phone_min'], 'max:'.$spec['phone_max'], 'regex:'.$phoneRegex],
            'passengers.*.nationality' => ['nullable', 'string', 'min:'.$spec['nationality_min'], 'max:'.$spec['nationality_max'], 'regex:'.$nationalityRegex],
            'passengers.0.email' => ['required', 'email', 'max:120'],
            'passengers.0.phone' => ['required', 'string', 'min:'.$spec['phone_min'], 'max:'.$spec['phone_max'], 'regex:'.$phoneRegex],
            'passengers.0.nationality' => ['required', 'string', 'min:'.$spec['nationality_min'], 'max:'.$spec['nationality_max'], 'regex:'.$nationalityRegex],
            'country_code' => ['nullable', 'string', 'max:'.$spec['country_code_max'], 'regex:'.$countryRegex],
            'form_of_payment' => ['nullable', 'in:Cash,Credit,Check'],
        ];

        if ($provider === self::DOWNTOWN_TRAVEL) {
            $iso = self::isoAlpha2Nationalities();
            $rules['passengers.*.nationality'] = ['nullable', 'string', 'size:2', 'in:'.implode(',', $iso)];
            $rules['passengers.0.nationality'] = ['required', 'string', 'size:2', 'in:'.implode(',', $iso)];
        }

        if ($spec['docs_required']) {
            $rules['passengers.*.national_id'] = [
                'required', 'string',
                'min:'.(int) $spec['national_id_min'],
                'max:'.(int) $spec['national_id_max'],
                'regex:/^[0-9]{'.(int) $spec['national_id_min'].','.(int) $spec['national_id_max'].'}$/',
            ];
            $rules['passengers.*.passport_number'] = [
                'required', 'string',
                'min:'.$spec['passport_min'],
                'max:'.$spec['passport_max'],
                'regex:/^[A-Za-z0-9]{'.$spec['passport_min'].','.$spec['passport_max'].'}$/',
            ];
            $rules['passengers.*.passport_expire'] = ['required', 'date', 'after:today'];
        } else {
            $rules['passengers.*.national_id'] = ['nullable', 'string', 'max:32'];
            $rules['passengers.*.passport_number'] = [
                'nullable', 'string',
                'min:'.$spec['passport_min'],
                'max:'.$spec['passport_max'],
                'regex:/^[A-Za-z0-9]{'.$spec['passport_min'].','.$spec['passport_max'].'}$/',
            ];
            $rules['passengers.*.passport_expire'] = ['nullable', 'date', 'after:today', 'required_with:passengers.*.passport_number'];
        }

        return $rules;
    }

    public static function isReady(): bool
    {
        return match (self::current()) {
            self::SUNSPRING => SunSpringIntegrationConfig::isReadyForAir(),
            self::DOWNTOWN_TRAVEL => DowntownTravelIntegrationConfig::isReadyForAir(),
            default => TravelportIntegrationConfig::isReadyForAir(),
        };
    }

    public static function label(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::SUNSPRING => 'SunSpring',
            self::DOWNTOWN_TRAVEL => 'Downtown Travel',
            default => 'Travelport',
        };
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    public static function fromResult(?array $result = null): string
    {
        $fromResult = strtolower((string) ($result['provider'] ?? ''));
        if (in_array($fromResult, self::all(), true)) {
            return $fromResult;
        }

        if ($fromResult === 'mixed') {
            $fromSolution = strtolower((string) data_get($result, 'solutions.0.provider', ''));
            if (in_array($fromSolution, self::all(), true)) {
                return $fromSolution;
            }
        }

        $fromSolution = strtolower((string) data_get($result, 'solutions.0.provider', ''));
        if (in_array($fromSolution, self::all(), true)) {
            return $fromSolution;
        }

        return self::current();
    }

    /**
     * @return array{key: string, label: string, css: string}
     */
    public static function normalizeEnvironment(mixed $raw): array
    {
        $value = strtolower(trim((string) $raw));
        $isLive = in_array($value, ['production', 'prod', 'live'], true);

        return [
            'key' => $isLive ? 'live' : 'sandbox',
            'label' => $isLive ? 'Live' : 'Sandbox',
            'css' => $isLive ? 'env-badge env-badge--live' : 'env-badge env-badge--sandbox',
        ];
    }

    /**
     * Effective Live / Sandbox mode for a flight provider.
     *
     * @return array{key: string, label: string, css: string}
     */
    public static function environmentMode(?string $provider = null): array
    {
        $provider = strtolower((string) ($provider ?? self::current()));

        try {
            $raw = match ($provider) {
                self::SUNSPRING => SunSpringIntegrationConfig::merged()['environment'] ?? 'sandbox',
                self::DOWNTOWN_TRAVEL => DowntownTravelIntegrationConfig::merged()['environment'] ?? 'sandbox',
                default => TravelportIntegrationConfig::merged()['environment'] ?? 'pp',
            };
        } catch (\Throwable) {
            $raw = 'sandbox';
        }

        return self::normalizeEnvironment($raw);
    }

    /**
     * @return array{id: string, label: string, short: string, css: string}
     */
    public static function badge(?string $provider = null): array
    {
        $id = strtolower((string) ($provider ?? self::current()));
        if (! in_array($id, self::all(), true)) {
            $id = self::TRAVELPORT;
        }

        return match ($id) {
            self::SUNSPRING => [
                'id' => self::SUNSPRING,
                'label' => 'SunSpring',
                'short' => 'SunSpring',
                'css' => 'provider-badge provider-badge--sunspring',
            ],
            self::DOWNTOWN_TRAVEL => [
                'id' => self::DOWNTOWN_TRAVEL,
                'label' => 'Downtown Travel',
                'short' => 'Downtown Travel',
                'css' => 'provider-badge provider-badge--downtown',
            ],
            default => [
                'id' => self::TRAVELPORT,
                'label' => 'Travelport',
                'short' => 'Travelport',
                'css' => 'provider-badge provider-badge--travelport',
            ],
        };
    }

    /**
     * @return list<array{id: string, label: string, ready: bool}>
     */
    public static function options(): array
    {
        return [
            [
                'id' => self::TRAVELPORT,
                'label' => 'Travelport',
                'ready' => TravelportIntegrationConfig::isReadyForAir(),
            ],
            [
                'id' => self::SUNSPRING,
                'label' => 'SunSpring',
                'ready' => SunSpringIntegrationConfig::isReadyForAir(),
            ],
            [
                'id' => self::DOWNTOWN_TRAVEL,
                'label' => 'Downtown Travel',
                'ready' => DowntownTravelIntegrationConfig::isReadyForAir(),
            ],
        ];
    }
}
