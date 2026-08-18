@extends('frontend.layouts.tavelo')

@section('title', 'Hotel Results')

@section('content')
@include('frontend.hotels.partials.search-form', [
    'hotelSearchInput' => $hotelSearchInput ?? [],
    'hotelReady' => $hotelReady ?? false,
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
            <h4 class="mb-0">
                {{ count($hotelSearchResult['solutions'] ?? []) }} hotel option(s)
                <span class="badge bg-secondary">API: Xconnect</span>
            </h4>
            <a href="{{ route('frontend.hotels.hub') }}" class="btn btn-sm btn-outline-secondary">New search</a>
        </div>

        @if(!empty($hotelSearchResult['ok']) && !empty($hotelSearchResult['solutions']))
            <div class="row g-3">
                @foreach($hotelSearchResult['solutions'] as $sol)
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <h5 class="mb-1">{{ $sol['hotel_name'] ?? ('Hotel #'.($sol['hotel_id'] ?? '')) }}</h5>
                                    <p class="small text-muted mb-1">Hotel ID {{ $sol['hotel_id'] ?? '—' }} · Option {{ $sol['hotel_option_id'] ?? '—' }}</p>
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
@endsection
