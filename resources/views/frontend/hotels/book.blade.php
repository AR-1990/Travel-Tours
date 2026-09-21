@extends('frontend.layouts.tavelo')

@section('title', 'Book Hotel')

@section('content')
@php
    $prebook = $priced['prebook'] ?? [];
    $solution = $priced['solution'] ?? [];
@endphp
<div class="pt-80 pb-80">
    <div class="container" style="max-width: 720px;">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2 class="mb-3">Guest details</h2>
        <div class="border rounded p-3 mb-4 bg-white">
            <h5 class="mb-1">{{ $prebook['hotel_name'] ?? $solution['hotel_name'] ?? 'Hotel' }}</h5>
            <p class="mb-0 text-muted">
                @if(!empty($prebook['city']) || !empty($solution['city']) || !empty($solution['destination_label']))
                    {{ $prebook['city'] ?? $solution['city'] ?? $solution['destination_label'] }} ·
                @endif
                {{ $solution['check_in'] ?? '' }} → {{ $solution['check_out'] ?? '' }}
                · {{ number_format((float) ($priced['total_price'] ?? 0), 2) }} {{ $priced['currency'] ?? '' }}
            </p>
        </div>

        <form method="POST" action="{{ route('frontend.hotels.book.store') }}" class="border rounded p-4 bg-white">
            @csrf
            <input type="hidden" name="provider" value="{{ $hotelProvider ?? \App\Support\HotelProvider::current() }}">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Prefix</label>
                    <select name="prefix" class="form-select" required>
                        @foreach(['Mr.','Mrs.','Ms.','Miss.'] as $p)
                            <option value="{{ $p }}" @selected(old('prefix', 'Mr.') === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">First name (lead)</label>
                    <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Last name (lead)</label>
                    <input type="text" name="last_name" class="form-control" required value="{{ old('last_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">2nd adult first (optional)</label>
                    <input type="text" name="guest2_first" class="form-control" value="{{ old('guest2_first') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">2nd adult last (optional)</label>
                    <input type="text" name="guest2_last" class="form-control" value="{{ old('guest2_last') }}">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="theme-btn">Confirm booking</button>
                <a href="{{ route('frontend.hotels.results') }}" class="btn btn-outline-secondary">Back</a>
            </div>
            <p class="small text-muted mt-3 mb-0">Use a real-looking name. Prefer refundable rates and cancel test bookings via the API.</p>
        </form>
    </div>
</div>
@endsection
