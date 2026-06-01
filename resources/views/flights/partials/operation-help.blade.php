@php
    $op = $currentOperation ?? [];
    $status = $op['status'] ?? 'ready';
@endphp
<div class="card-modern mb-3 border-0 bg-light">
    <div class="card-body py-3">
        <p class="mb-2">{{ $op['help'] ?? $op['description'] ?? '' }}</p>
        @if($status === 'beta')
            <p class="small text-warning mb-0">
                <i class="fas fa-flask me-1"></i>
                <strong>Preview:</strong> This flight API sends a basic request. Some fields may still need to be added for your GDS workflow.
            </p>
        @elseif($status === 'ready')
            <p class="small text-muted mb-0">
                <i class="fas fa-check-circle text-success me-1"></i> Ready to use with your Travelport credentials.
            </p>
        @endif
        @if(($showDevPanel ?? false) && !empty($op['request']))
            <details class="mt-2 mb-0">
                <summary class="small text-muted" style="cursor:pointer">Technical (API message names)</summary>
                <p class="small text-muted mb-0 mt-1"><code>{{ $op['request'] }}</code> → <code>{{ $op['response'] ?? '' }}</code></p>
            </details>
        @endif
    </div>
</div>
