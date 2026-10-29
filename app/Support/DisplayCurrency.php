<?php

namespace App\Support;

use App\Models\System\Tenant;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Resolve the currency shown on booking / payment UI.
 * Agency: tenant-assigned currency. B2C: visitor region (geo / Accept-Language / Travelport region).
 */
final class DisplayCurrency
{
    /**
     * @var array<string, string>
     */
    private const COUNTRY_CURRENCY = [
        'PK' => 'PKR',
        'IN' => 'INR',
        'BD' => 'BDT',
        'AE' => 'AED',
        'SA' => 'SAR',
        'QA' => 'QAR',
        'KW' => 'KWD',
        'BH' => 'BHD',
        'OM' => 'OMR',
        'GB' => 'GBP',
        'IE' => 'EUR',
        'FR' => 'EUR',
        'DE' => 'EUR',
        'IT' => 'EUR',
        'ES' => 'EUR',
        'NL' => 'EUR',
        'BE' => 'EUR',
        'PT' => 'EUR',
        'AT' => 'EUR',
        'CH' => 'CHF',
        'TR' => 'TRY',
        'US' => 'USD',
        'CA' => 'CAD',
        'MX' => 'MXN',
        'AU' => 'AUD',
        'NZ' => 'NZD',
        'JP' => 'JPY',
        'CN' => 'CNY',
        'HK' => 'HKD',
        'SG' => 'SGD',
        'MY' => 'MYR',
        'TH' => 'THB',
        'ID' => 'IDR',
        'PH' => 'PHP',
        'KR' => 'KRW',
        'EG' => 'EGP',
        'ZA' => 'ZAR',
        'NG' => 'NGN',
        'KE' => 'KES',
        'BR' => 'BRL',
        'AR' => 'ARS',
    ];

    public static function resolve(?Authenticatable $user = null): string
    {
        $tenantId = $user && isset($user->tenant_id) ? (int) $user->tenant_id : 0;
        if ($tenantId > 0) {
            $assigned = strtoupper(trim((string) (Tenant::query()->whereKey($tenantId)->value('currency') ?: '')));
            if ($assigned !== '') {
                return $assigned;
            }
        }

        return self::forRegion();
    }

    public static function forRegion(?string $country = null): string
    {
        $country = strtoupper(trim((string) ($country ?: self::detectCountry())));
        if ($country !== '' && isset(self::COUNTRY_CURRENCY[$country])) {
            return self::COUNTRY_CURRENCY[$country];
        }

        return self::fromTravelportRegion();
    }

    public static function detectCountry(): string
    {
        $request = request();
        if (! $request) {
            return '';
        }

        foreach (['CF-IPCountry', 'X-AppEngine-Country', 'X-Country-Code', 'CloudFront-Viewer-Country'] as $header) {
            $code = strtoupper(trim((string) $request->header($header, '')));
            if (preg_match('/^[A-Z]{2}$/', $code) && $code !== 'XX') {
                return $code;
            }
        }

        $accept = (string) $request->header('Accept-Language', '');
        if ($accept !== '' && preg_match('/[a-z]{2}[-_]([a-z]{2})/i', $accept, $m)) {
            return strtoupper($m[1]);
        }

        return '';
    }

    public static function fromTravelportRegion(): string
    {
        $region = strtolower(trim((string) config('travelport.region', 'emea')));

        return match ($region) {
            'americas' => 'USD',
            'apac' => 'AED',
            default => 'EUR', // emea
        };
    }
}
