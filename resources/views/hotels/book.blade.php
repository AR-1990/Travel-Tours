@extends('admin.layouts.main')

@section('title', 'Book hotel')

@section('content')
@php
    $prebook = $priced['prebook'] ?? [];
    $solution = $priced['solution'] ?? [];
    $providerId = $hotelProvider ?? \App\Support\HotelProvider::current();
    $spec = \App\Support\HotelProvider::bookFieldSpecs($providerId);
    $prefix = $hotelsRoutePrefix ?? 'admin';
@endphp
<div class="container-fluid panel-page">
    @include('hotels.partials.nav')
    @include('hotels.partials.workflow-steps', ['workflowStep' => 'book'])
    @include('admin.partials.page-header', [
        'title' => 'Guest details',
        'subtitle' => 'Confirm the lead guest and contact details to complete the booking.',
        'icon' => 'fas fa-user',
    ])
    @include('admin.partials.flash')

    <div class="panel-surface mb-4">
        <h2 class="h5 mb-1">{{ $prebook['hotel_name'] ?? $solution['hotel_name'] ?? 'Hotel' }}</h2>
        <p class="mb-0 text-muted">
            @if(!empty($prebook['city']) || !empty($solution['city']) || !empty($solution['destination_label']))
                {{ $prebook['city'] ?? $solution['city'] ?? $solution['destination_label'] }} ·
            @endif
            {{ $solution['check_in'] ?? '' }} → {{ $solution['check_out'] ?? '' }}
            · {{ number_format((float) ($priced['total_price'] ?? 0), 2) }} {{ $priced['currency'] ?? '' }}
        </p>
        <p class="small mb-0 mt-1">{{ \App\Support\HotelProvider::label($providerId) }}</p>
    </div>

    <form method="POST" action="{{ route($prefix . '.hotels.book.store') }}" class="panel-surface">
        @csrf
        <input type="hidden" name="provider" value="{{ $providerId }}">
        <p class="small text-muted mb-3">{{ $spec['hint'] }}</p>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Prefix <span class="text-danger">*</span></label>
                <select name="prefix" class="form-select" required>
                    @foreach(['Mr.','Mrs.','Ms.','Miss.'] as $p)
                        <option value="{{ $p }}" @selected(old('prefix', 'Mr.') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">First name (lead) <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control"
                    required value="{{ old('first_name') }}"
                    minlength="{{ $spec['name_min'] }}"
                    maxlength="{{ $spec['name_max'] }}"
                    pattern="{{ $spec['name_pattern'] }}"
                    title="{{ $spec['name_title'] }}"
                    autocomplete="given-name">
            </div>
            <div class="col-md-5">
                <label class="form-label">Last name (lead) <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control"
                    required value="{{ old('last_name') }}"
                    minlength="{{ $spec['name_min'] }}"
                    maxlength="{{ $spec['name_max'] }}"
                    pattern="{{ $spec['name_pattern'] }}"
                    title="{{ $spec['name_title'] }}"
                    autocomplete="family-name">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required maxlength="120" value="{{ old('email') }}" autocomplete="email">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
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
                    <label class="form-label">Nationality <span class="text-danger">*</span></label>
                    <select name="nationality" class="form-select" required>
                        @foreach(\App\Support\HotelProvider::nationalityOptions() as $code => $label)
                            <option value="{{ $code }}" @selected(old('nationality', 'US') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-6">
                <label class="form-label">2nd adult first <span class="text-muted">(optional)</span></label>
                <input type="text" name="guest2_first" class="form-control"
                    value="{{ old('guest2_first') }}"
                    minlength="{{ $spec['name_min'] }}"
                    maxlength="{{ $spec['name_max'] }}"
                    pattern="{{ $spec['name_pattern'] }}"
                    title="{{ $spec['name_title'] }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">2nd adult last <span class="text-muted">(optional)</span></label>
                <input type="text" name="guest2_last" class="form-control"
                    value="{{ old('guest2_last') }}"
                    minlength="{{ $spec['name_min'] }}"
                    maxlength="{{ $spec['name_max'] }}"
                    pattern="{{ $spec['name_pattern'] }}"
                    title="{{ $spec['name_title'] }}">
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Confirm booking</button>
            <a href="{{ route($prefix . '.hotels.search') }}" class="btn btn-outline-secondary">Back to search</a>
        </div>
    </form>
</div>
@endsection
