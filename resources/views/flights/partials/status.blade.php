@php
    $anyReady = $anyProviderReady ?? (($travelportReady ?? false) || ($sunspringReady ?? false));
    $tpReady = $travelportReady ?? false;
    $ssReady = $sunspringReady ?? false;
@endphp

@if(! $anyReady)
    <div class="alert alert-warning d-flex align-items-start gap-2 border-0 shadow-sm">
        <i class="fas fa-key mt-1"></i>
        <div>
            <strong>Setup required.</strong> Configure at least one flight provider under Integrations.
            @if(($flightsRoutePrefix ?? '') === 'admin')
                <a href="{{ route('admin.integrations.index') }}" class="alert-link">Open Integrations</a>
            @else
                Contact your platform administrator.
            @endif
        </div>
    </div>
@else
    <div class="d-flex flex-wrap gap-2 mb-3">
        @if($tpReady)
            <span class="status-pill ok"><i class="fas fa-check-circle"></i> Travelport ready</span>
        @else
            <span class="status-pill text-muted" style="background:#f3f4f6;color:#6b7280!important"><i class="fas fa-minus-circle"></i> Travelport off</span>
        @endif
        @if($ssReady)
            <span class="status-pill ok"><i class="fas fa-check-circle"></i> SunSpring ready</span>
        @else
            <span class="status-pill text-muted" style="background:#f3f4f6;color:#6b7280!important"><i class="fas fa-minus-circle"></i> SunSpring off</span>
        @endif
        <span class="status-pill ok text-muted" style="background:#f3f4f6;color:#4b5563!important"><i class="fas fa-cloud"></i> Live search</span>
    </div>
@endif
