@extends('admin.layouts.main')

@section('title', 'Book flight')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Search</a></li>
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}">Price</a></li>
            <li class="breadcrumb-item active">Book</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1><i class="fas fa-user me-2"></i>Passenger details</h1>
        <p class="mb-0">Enter traveler information to create the reservation.</p>
    </div>

    @include('flights.partials.workflow-steps', ['workflowStep' => 'book'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
            @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <div>{{ session('error') }}</div>
            @if(session('travelport_last_error_reason'))
                <div class="small text-muted mt-1">Reason: {{ session('travelport_last_error_reason') }}</div>
            @endif
            @if(($showDevPanel ?? false) && session('travelport_last_error_excerpt'))
                <details class="mt-2 small">
                    <summary>Technical details (admin)</summary>
                    <pre class="mt-2 mb-0 small bg-light p-2 rounded" style="max-height:200px;overflow:auto;">{{ session('travelport_last_error_excerpt') }}</pre>
                </details>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('flights.partials.status')

    @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.book.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Title</label>
                        <select name="passenger_prefix" class="form-select form-select-sm">
                            @foreach(['Mr', 'Mrs', 'Ms', 'Miss'] as $title)
                                <option value="{{ $title }}" @selected(old('passenger_prefix', $bookInput['passenger_prefix'] ?? 'Mr') === $title)>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First name</label>
                        <input type="text" name="passenger_first" class="form-control form-control-sm" value="{{ old('passenger_first', $bookInput['passenger_first'] ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last name</label>
                        <input type="text" name="passenger_last" class="form-control form-control-sm" value="{{ old('passenger_last', $bookInput['passenger_last'] ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="passenger_gender" class="form-select form-select-sm" required>
                            <option value="M" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? 'M') === 'M')>Male</option>
                            <option value="F" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? '') === 'F')>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of birth</label>
                        <input type="date" name="passenger_dob" class="form-control form-control-sm" value="{{ old('passenger_dob', $bookInput['passenger_dob'] ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="passenger_email" class="form-control form-control-sm" value="{{ old('passenger_email', $bookInput['passenger_email'] ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="passenger_phone" class="form-control form-control-sm" value="{{ old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Form of payment</label>
                        <select name="form_of_payment" class="form-select form-select-sm">
                            <option value="Cash">Cash</option>
                            <option value="Credit">Credit</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm" @disabled(!($travelportReady ?? false))>
                        <i class="fas fa-check me-1"></i> Confirm booking &amp; view reservation
                    </button>
                    <a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}" class="btn btn-outline-secondary btn-sm">Back to price</a>
                </div>
                <p class="small text-muted mt-2 mb-0">After you confirm, reservation details open automatically.</p>
            </form>
        </div>
    </div>
</div>
@endsection
