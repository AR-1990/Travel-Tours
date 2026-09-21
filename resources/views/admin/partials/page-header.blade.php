@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $icon = $icon ?? null;
    $actions = $actions ?? null;
@endphp
<div class="panel-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1>
            @if($icon)
                <i class="{{ $icon }} me-2"></i>
            @endif
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if(! empty($actions))
        <div class="d-flex flex-wrap gap-2 align-items-center">
            {!! $actions !!}
        </div>
    @endif
</div>
