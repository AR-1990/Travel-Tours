@extends('frontend.layouts.tavelo')

@section('title', 'Book Hotel')

@section('content')
@php
    $prebook = $priced['prebook'] ?? [];
    $solution = $priced['solution'] ?? [];
    $providerId = $hotelProvider ?? \App\Support\HotelProvider::current();
    $spec = \App\Support\HotelProvider::bookFieldSpecs($providerId);
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
            <p class="small mb-0 mt-1">{{ \App\Support\HotelProvider::label($providerId) }}</p>
        </div>

        <form method="POST" action="{{ route('frontend.hotels.book.store') }}" class="border rounded p-4 bg-white">
            @csrf
            <input type="hidden" name="provider" value="{{ $providerId }}">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="small text-muted mb-3">{{ $spec['hint'] }}</p>
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
                    <input type="text" name="first_name" class="form-control"
                        required value="{{ old('first_name') }}"
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}"
                        autocomplete="given-name">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Last name (lead)</label>
                    <input type="text" name="last_name" class="form-control"
                        required value="{{ old('last_name') }}"
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}"
                        autocomplete="family-name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required maxlength="120" value="{{ old('email') }}" autocomplete="email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control"
                        required value="{{ old('phone') }}"
                        minlength="{{ $spec['phone_min'] }}"
                        maxlength="{{ $spec['phone_max'] }}"
                        pattern="{{ $spec['phone_pattern'] }}"
                        title="{{ $spec['phone_title'] }}"
                        autocomplete="tel"
                        placeholder="+15551234567">
                </div>
                @if(!empty($spec['requires_nationality']))
                    <div class="col-md-6">
                        <label class="form-label">Nationality</label>
                        <select name="nationality" class="form-select" required>
                            @foreach(\App\Support\HotelProvider::nationalityOptions() as $code => $label)
                                <option value="{{ $code }}" @selected(old('nationality', 'US') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">2nd adult first (optional)</label>
                    <input type="text" name="guest2_first" class="form-control"
                        value="{{ old('guest2_first') }}"
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">2nd adult last (optional)</label>
                    <input type="text" name="guest2_last" class="form-control"
                        value="{{ old('guest2_last') }}"
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="theme-btn">Confirm booking</button>
                <a href="{{ route('frontend.hotels.results') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
