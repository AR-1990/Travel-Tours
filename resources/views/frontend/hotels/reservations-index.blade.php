@extends('frontend.layouts.tavelo')

@section('title', 'Hotel Bookings')

@section('content')
<div class="pt-80 pb-80">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Hotel bookings</h2>
            <a href="{{ route('frontend.hotels.hub') }}" class="theme-btn btn-sm">New search</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                        placeholder="Reference, hotel, guest, city…">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="confirmed" @selected(($filters['status'] ?? '') === 'confirmed')>Confirmed</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Provider</label>
                    <select name="provider" class="form-select">
                        <option value="">All providers</option>
                        @foreach(($providerOptions ?? \App\Support\HotelProvider::options()) as $option)
                            <option value="{{ $option['id'] }}" @selected(($filters['provider'] ?? '') === $option['id'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="theme-btn w-100">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive border rounded bg-white">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Hotel</th>
                        <th>Dates</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $row)
                        <tr>
                            <td>{{ $row->referenceLabel() }}</td>
                            <td>
                                {{ $row->hotel_name }}
                                @if($row->city_id)
                                    <div class="small text-muted">{{ $row->city_id }}</div>
                                @endif
                            </td>
                            <td>{{ $row->datesLabel() }}</td>
                            <td>{{ $row->providerLabel() }}</td>
                            <td><span class="badge {{ $row->statusBadgeClass() }}">{{ $row->statusLabel() }}</span></td>
                            <td>{{ $row->total_price }} {{ $row->currency }}</td>
                            <td><a href="{{ route('frontend.hotels.reservations.show', $row->id) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hotel bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $reservations->links() }}</div>
    </div>
</div>
@endsection
