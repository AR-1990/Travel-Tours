@php
    $step = $workflowStep ?? 'search';
    $prefix = $hotelsRoutePrefix ?? 'admin';
    $steps = [
        'search' => ['label' => 'Search', 'route' => $prefix.'.hotels.search'],
        'book' => ['label' => 'Guest details', 'route' => $prefix.'.hotels.book'],
        'confirmation' => ['label' => 'Confirmation', 'route' => $prefix.'.hotels.confirmation'],
    ];
    $order = array_keys($steps);
    $currentIdx = array_search($step, $order, true);
    if ($currentIdx === false) {
        $currentIdx = 0;
    }
@endphp
<ol class="breadcrumb small mb-3 flex-wrap">
    @foreach($steps as $key => $meta)
        @php $idx = array_search($key, $order, true); @endphp
        <li class="breadcrumb-item {{ $key === $step ? 'active' : '' }}">
            @if($idx < $currentIdx && \Illuminate\Support\Facades\Route::has($meta['route']))
                <a href="{{ route($meta['route']) }}">{{ $meta['label'] }}</a>
            @else
                {{ $meta['label'] }}
            @endif
        </li>
    @endforeach
</ol>
