@if(!$travelportEnabled)
    <div class="alert alert-warning d-flex align-items-start gap-2 border-0 shadow-sm">
        <i class="fas fa-exclamation-triangle mt-1"></i>
        <div>
            <strong>Travelport is disabled.</strong>
            @if($flightsRoutePrefix === 'admin')
                Enable it under <a href="{{ route('admin.integrations.index') }}" class="alert-link">Integrations</a>.
            @else
                Contact your platform administrator.
            @endif
        </div>
    </div>
@elseif(!$travelportReady)
    <div class="alert alert-warning d-flex align-items-start gap-2 border-0 shadow-sm">
        <i class="fas fa-key mt-1"></i>
        <div>
            <strong>Setup required.</strong> Air search needs API username, password, and target branch.
            @if($flightsRoutePrefix === 'admin')
                <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="alert-link">Configure Travelport</a>
            @endif
        </div>
    </div>
@else
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="status-pill ok"><i class="fas fa-check-circle"></i> GDS connected</span>
        <span class="status-pill ok text-muted" style="background:#f3f4f6;color:#4b5563!important"><i class="fas fa-cloud"></i> Live search</span>
    </div>
@endif
