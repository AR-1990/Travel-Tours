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

        <div class="table-responsive border rounded bg-white">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Hotel</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $row)
                        <tr>
                            <td>{{ $row->reference_no ?: $row->internal_reference }}</td>
                            <td>
                                {{ $row->hotel_name }}
                                @if($row->city_id)
                                    <div class="small text-muted">{{ $row->city_id }}</div>
                                @endif
                            </td>
                            <td>{{ optional($row->check_in)->toDateString() }} → {{ optional($row->check_out)->toDateString() }}</td>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->total_price }} {{ $row->currency }}</td>
                            <td><a href="{{ route('frontend.hotels.reservations.show', $row->id) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No hotel bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $reservations->links() }}</div>
    </div>
</div>
@endsection
