@php
    $envRaw = strtolower(trim((string) ($environment ?? 'sandbox')));
    $isLive = in_array($envRaw, ['production', 'prod', 'live'], true);
@endphp
<span class="badge {{ $isLive ? 'bg-success' : 'bg-primary' }}">
    {{ $isLive ? 'Live' : 'Sandbox' }}
</span>
