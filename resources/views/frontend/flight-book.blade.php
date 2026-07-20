@extends('frontend.layouts.tavelo')

@section('title', 'Book flight — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">Passenger details</h1>
                    <p class="mb-0">Complete your booking</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @include('frontend.partials.flight-workflow-steps', ['workflowStep' => 'book'])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('frontend.flights.book.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Title</label>
                                <select name="passenger_prefix" class="form-control">
                                    @foreach(['Mr', 'Mrs', 'Ms', 'Miss'] as $title)
                                        <option value="{{ $title }}" @selected(old('passenger_prefix', $bookInput['passenger_prefix'] ?? 'Mr') === $title)>{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First name</label>
                                <input type="text" name="passenger_first" class="form-control" value="{{ old('passenger_first', $bookInput['passenger_first'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last name</label>
                                <input type="text" name="passenger_last" class="form-control" value="{{ old('passenger_last', $bookInput['passenger_last'] ?? '') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Gender</label>
                                <select name="passenger_gender" class="form-control" required>
                                    <option value="M" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? 'M') === 'M')>Male</option>
                                    <option value="F" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? '') === 'F')>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of birth</label>
                                <input type="date" name="passenger_dob" class="form-control" value="{{ old('passenger_dob', $bookInput['passenger_dob'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="passenger_email" class="form-control" value="{{ old('passenger_email', $bookInput['passenger_email'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="passenger_phone" class="form-control" value="{{ old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Form of payment</label>
                                <select name="form_of_payment" class="form-control">
                                    <option value="Cash">Cash</option>
                                    <option value="Credit">Credit</option>
                                    <option value="Check">Check</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="theme-btn" @disabled(!($travelportReady ?? false))>
                                Confirm booking &amp; view reservation<i class="fas fa-check"></i>
                            </button>
                            <a href="{{ route('frontend.flights.price.show') }}" class="theme-btn theme-btn-outline">
                                Back to price<i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <p class="small text-muted mt-2 mb-0">After you confirm, reservation details open automatically.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
