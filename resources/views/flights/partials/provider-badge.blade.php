@php
    $providerId = $provider
        ?? (isset($sol) && is_array($sol) ? ($sol['provider'] ?? null) : null)
        ?? (isset($searchResult) && is_array($searchResult) ? ($searchResult['provider'] ?? null) : null)
        ?? (isset($flightSearchResult) && is_array($flightSearchResult) ? ($flightSearchResult['provider'] ?? null) : null)
        ?? (isset($flightPriceResult) && is_array($flightPriceResult) ? ($flightPriceResult['provider'] ?? null) : null)
        ?? (isset($reservation) && is_object($reservation) ? $reservation->provider() : null)
        ?? ($flightProvider ?? \App\Support\FlightProvider::current());
    $badge = \App\Support\FlightProvider::badge($providerId);
    $envKey = $environment
        ?? (isset($sol) && is_array($sol) ? ($sol['environment'] ?? null) : null)
        ?? (isset($sourceMeta) && is_array($sourceMeta) ? ($sourceMeta['environment'] ?? null) : null);
    $env = ($envKey !== null && $envKey !== '')
        ? \App\Support\FlightProvider::normalizeEnvironment($envKey)
        : \App\Support\FlightProvider::environmentMode($providerId);
    $size = $size ?? 'md';
@endphp
@once
<style>
.provider-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .7rem;border-radius:999px;font-size:.75rem;font-weight:600;letter-spacing:.02em;border:1px solid transparent;white-space:nowrap}
.provider-badge--sm{font-size:.68rem;padding:.2rem .55rem}
.provider-badge--travelport{background:#e8eef8;color:#0c2c7a;border-color:#9db4d8}
.provider-badge--sunspring{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
.provider-badge--downtown{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
.env-badge{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:999px;font-size:.68rem;font-weight:700;letter-spacing:.03em;border:1px solid transparent;white-space:nowrap}
.env-badge--live{background:#dcfce7;color:#166534;border-color:#86efac}
.env-badge--sandbox{background:#dbeafe;color:#1e40af;border-color:#93c5fd}
</style>
@endonce
<span class="d-inline-flex align-items-center gap-1 flex-wrap">
    <span class="{{ $badge['css'] }} {{ $size === 'sm' ? 'provider-badge--sm' : '' }}" title="{{ $badge['label'] }}">
        {{ $badge['label'] }}
    </span>
    <span class="{{ $env['css'] }}" title="{{ $env['label'] }} environment">{{ $env['label'] }}</span>
</span>
