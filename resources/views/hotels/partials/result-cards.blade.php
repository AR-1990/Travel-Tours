@php
    $result = $hotelSearchResult ?? null;
    $resultProvider = strtolower((string) (is_array($result) ? ($result['provider'] ?? ($hotelSearchInput['provider'] ?? 'xconnect')) : 'xconnect'));
    $providerBadge = \App\Support\HotelProvider::badge($resultProvider);
    $envBadge = \App\Support\HotelProvider::environmentMode($resultProvider);
    $prefix = $hotelsRoutePrefix ?? 'admin';
@endphp
@if(is_array($result))
    <section class="mb-4" aria-label="Hotel search results">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="h5 mb-0 d-flex flex-wrap align-items-center gap-2">
                {{ count($result['solutions'] ?? []) }} hotel option(s)
                <span class="badge text-bg-light border">{{ $providerBadge['label'] }}</span>
                <span class="badge {{ $envBadge['key'] === 'live' ? 'text-bg-success' : 'text-bg-primary' }}">{{ $envBadge['label'] }}</span>
            </h2>
        </div>

        @if(!empty($result['ok']) && !empty($result['solutions']))
            <div class="row g-3">
                @foreach($result['solutions'] as $sol)
                    @php
                        $hotelName = trim((string) ($sol['hotel_name'] ?? '')) ?: 'Hotel';
                        $cityLabel = trim((string) ($sol['city'] ?? $sol['destination_label'] ?? ''));
                        $stars = trim((string) ($sol['star_rating'] ?? ''));
                        $solEnv = ! empty($sol['environment'])
                            ? \App\Support\HotelProvider::normalizeEnvironment($sol['environment'])
                            : $envBadge;
                    @endphp
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                                            <h3 class="h6 mb-0">{{ $hotelName }}</h3>
                                            <span class="badge {{ $solEnv['key'] === 'live' ? 'text-bg-success' : 'text-bg-primary' }}">{{ $solEnv['label'] }}</span>
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
                                        <form method="POST" action="{{ route($prefix . '.hotels.prebook') }}" class="mt-2">
                                            @csrf
                                            <input type="hidden" name="solution_key" value="{{ $sol['key'] }}">
                                            <button type="submit" class="btn btn-primary btn-sm">Select</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(!empty($result['ok']))
            <div class="alert alert-info mb-0">No availability for this city and dates. Try other dates a few months ahead.</div>
        @else
            <div class="alert alert-danger mb-0">{{ $result['message'] ?? 'Hotel search failed.' }}</div>
        @endif
    </section>
@endif
