@php
    $providerId = $provider
        ?? (isset($sol) && is_array($sol) ? ($sol['provider'] ?? null) : null)
        ?? (isset($searchResult) && is_array($searchResult) ? ($searchResult['provider'] ?? null) : null)
        ?? (isset($flightSearchResult) && is_array($flightSearchResult) ? ($flightSearchResult['provider'] ?? null) : null)
        ?? (isset($flightPriceResult) && is_array($flightPriceResult) ? ($flightPriceResult['provider'] ?? null) : null)
        ?? (isset($reservation) && is_object($reservation) ? $reservation->provider() : null)
        ?? ($flightProvider ?? \App\Support\FlightProvider::current());
    $badge = \App\Support\FlightProvider::badge($providerId);
    $size = $size ?? 'md';
@endphp
@once
<style>
.provider-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .7rem;border-radius:999px;font-size:.75rem;font-weight:600;letter-spacing:.02em;border:1px solid transparent;white-space:nowrap}
.provider-badge--sm{font-size:.68rem;padding:.2rem .55rem}
.provider-badge--travelport{background:#eef2ff;color:#3730a3;border-color:#c7d2fe}
.provider-badge--sunspring{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
</style>
@endonce
<span class="{{ $badge['css'] }} {{ $size === 'sm' ? 'provider-badge--sm' : '' }}" title="Results from {{ $badge['label'] }} API">
    <i class="fas fa-plug" aria-hidden="true"></i>
    {{ $badge['short'] }}
</span>
