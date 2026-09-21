@extends('frontend.layouts.tavelo')

@section('title', 'Hotel Results')

@section('content')
@php
    $resultProvider = strtolower((string) ($hotelSearchResult['provider'] ?? ($hotelSearchInput['provider'] ?? 'xconnect')));
    $providerBadge = \App\Support\HotelProvider::badge($resultProvider);
    $envBadge = \App\Support\HotelProvider::environmentMode($resultProvider);
@endphp
@include('frontend.hotels.partials.search-form', [
    'hotelSearchInput' => $hotelSearchInput ?? [],
    'hotelReady' => $hotelReady ?? false,
    'providerOptions' => $providerOptions ?? [],
    'hotelProvider' => $hotelProvider ?? $resultProvider,
    'downtownDestinations' => $downtownDestinations ?? [],
    'searchSubmitLabel' => 'Update Search',
])

<div class="pt-40 pb-80">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <h4 class="mb-0 d-flex flex-wrap align-items-center gap-2">
                {{ count($hotelSearchResult['solutions'] ?? []) }} hotel option(s)
                <span class="{{ $providerBadge['css'] }} provider-badge--sm">{{ $providerBadge['label'] }}</span>
                <span class="{{ $envBadge['css'] }}">{{ $envBadge['label'] }}</span>
            </h4>
            <a href="{{ route('frontend.hotels.hub') }}" class="btn btn-sm btn-outline-secondary">New search</a>
        </div>

        @if(!empty($hotelSearchResult['ok']) && !empty($hotelSearchResult['solutions']))
            <div class="row g-3">
                @foreach($hotelSearchResult['solutions'] as $sol)
                    @php
                        $hotelName = trim((string) ($sol['hotel_name'] ?? ''));
                        if ($hotelName === '') {
                            $hotelName = 'Hotel';
                        }
                        $cityLabel = trim((string) ($sol['city'] ?? $sol['destination_label'] ?? ''));
                        $stars = trim((string) ($sol['star_rating'] ?? ''));
                        $solEnv = ! empty($sol['environment'])
                            ? \App\Support\HotelProvider::normalizeEnvironment($sol['environment'])
                            : $envBadge;
                    @endphp
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                                        <h5 class="mb-0">{{ $hotelName }}</h5>
                                        <span class="{{ $solEnv['css'] }}">{{ $solEnv['label'] }}</span>
                                    </div>
                                    @if($cityLabel !== '')
                                        <p class="small text-muted mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $cityLabel }}
                                            @if($stars !== '') · {{ $stars }}★ @endif
                                        </p>
                                    @elseif(!empty($sol['hotel_id']))
                                        <p class="small text-muted mb-1">Hotel ID {{ $sol['hotel_id'] }}</p>
                                    @endif
                                    <p class="small mb-2">
                                        {{ $sol['check_in'] ?? '' }} → {{ $sol['check_out'] ?? '' }}
                                        @if(!empty($sol['nights'])) · {{ $sol['nights'] }} night(s) @endif
                                    </p>
                                    <ul class="small mb-0">
                                        @foreach(($sol['rooms'] ?? []) as $room)
                                            <li>
                                                Room {{ $room['RoomNo'] ?? '' }}:
                                                {{ $room['RoomTypeName'] ?? 'Room' }}
                                                · {{ $room['MappedMealName'] ?? $room['MealName'] ?? '' }}
                                                · {{ $room['Price'] ?? '' }} {{ $sol['currency'] ?? '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="text-end">
                                    <div class="fs-4 fw-semibold">
                                        {{ number_format((float) ($sol['total_price'] ?? 0), 2) }}
                                        <span class="fs-6">{{ $sol['currency'] ?? '' }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('frontend.hotels.prebook') }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="solution_key" value="{{ $sol['key'] }}">
                                        <button type="submit" class="theme-btn btn-sm">Select</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(!empty($hotelSearchResult['ok']))
            <div class="alert alert-info">No availability for this city and dates. Try other dates a few months ahead.</div>
        @else
            <div class="alert alert-danger">{{ $hotelSearchResult['message'] ?? 'Hotel search failed.' }}</div>
        @endif
    </div>
</div>
<style>
.provider-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .7rem;border-radius:999px;font-size:.75rem;font-weight:600;letter-spacing:.02em;border:1px solid transparent;white-space:nowrap}
.provider-badge--sm{font-size:.68rem;padding:.2rem .55rem}
.provider-badge--downtown{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
.provider-badge--xconnect{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.env-badge{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:999px;font-size:.68rem;font-weight:700;letter-spacing:.03em;border:1px solid transparent;white-space:nowrap}
.env-badge--live{background:#dcfce7;color:#166534;border-color:#86efac}
.env-badge--sandbox{background:#dbeafe;color:#1e40af;border-color:#93c5fd}
</style>
@endsection
