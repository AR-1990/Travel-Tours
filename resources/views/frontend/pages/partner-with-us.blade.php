@extends('frontend.layouts.tavelo')

@section('title', 'Partner With Us | Wise Trust Travel & Tourism')

@section('content')
@php
    $selectedType = old('partner_type', request('type'));
@endphp
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'Partner With Us',
        'current' => 'Partner With Us',
        'kicker' => 'Partnerships',
        'text' => 'Choose how you want to grow with Wise Trust Travel & Tourism — then apply through the contact form below.',
        'image' => asset('assets/img/hero/hero-3.jpg'),
    ])

    <section class="site-section partner-options">
        <div class="site-container site-container--wide">
            <div class="partner-options__intro">
                <h2 class="partner-options__title">Partner With Us &amp; Grow Together</h2>
                <p class="partner-options__subtitle">
                    Join our global network of partners and unlock endless opportunities. Choose the partnership type that suits your business.
                </p>
                <div class="partner-options__divider" aria-hidden="true">
                    <span></span>
                    <i class="far fa-plane"></i>
                    <span></span>
                </div>
            </div>

            <div class="partner-options__grid">
                @foreach($partnerTypes as $key => $partner)
                    <article class="partner-card partner-card--{{ $partner['tone'] }}" id="partner-{{ $key }}">
                        <div class="partner-card__icon">
                            <i class="{{ $partner['icon'] }}"></i>
                        </div>
                        <h3 class="partner-card__title">{{ $partner['number'] }}. {{ $partner['title'] }}</h3>
                        <p class="partner-card__text">{{ $partner['text'] }}</p>
                        <ul class="partner-card__features">
                            @foreach($partner['features'] as $feature)
                                <li><i class="fas fa-check-circle"></i><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <a
                            href="#partner-apply"
                            class="partner-card__btn partner-apply-btn"
                            data-partner-type="{{ $key }}"
                        >Apply Now</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface" id="partner-apply">
        <div class="site-container">
            @if(session('success'))
                <div class="alert alert-success home-flight-alert" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
            @endif

            <div class="site-grid site-grid--2">
                <div>
                    <span class="site-kicker">Application</span>
                    <h2 class="site-heading">Contact form</h2>
                    <p class="site-lead" style="margin-bottom: 1rem;">
                        One form for every partnership type. Tell us who you are and how you want to work with us.
                    </p>
                    <ul class="site-contact-list">
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>B2B &amp; B2C partners</strong>
                                <span>Agencies, consultants, and consumer brands welcome.</span>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>API &amp; Whitelabel</strong>
                                <span>Build or brand your travel experience on our stack.</span>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>Suppliers</strong>
                                <span>Bring inventory and grow distribution with Wise Trust.</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="site-panel">
                    <form class="site-form" method="POST" action="{{ route('pages.partner-with-us.submit') }}">
                        @csrf
                        <div class="site-form__group">
                            <label class="site-form__label" for="partner_type">Partnership type</label>
                            <select class="site-form__input" id="partner_type" name="partner_type" required>
                                <option value="">Select partnership</option>
                                @foreach($partnerTypes as $key => $partner)
                                    <option value="{{ $key }}" @selected($selectedType === $key)>{{ $partner['title'] }}</option>
                                @endforeach
                            </select>
                            @error('partner_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="partner_name">Full name</label>
                            <input class="site-form__input" id="partner_name" type="text" name="name" value="{{ old('name') }}" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="site-form__row">
                            <div class="site-form__group">
                                <label class="site-form__label" for="partner_email">Email</label>
                                <input class="site-form__input" id="partner_email" type="email" name="email" value="{{ old('email') }}" required>
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="site-form__group">
                                <label class="site-form__label" for="partner_phone">Phone</label>
                                <input class="site-form__input" id="partner_phone" type="text" name="phone" value="{{ old('phone') }}">
                                @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="partner_company">Company / brand</label>
                            <input class="site-form__input" id="partner_company" type="text" name="company" value="{{ old('company') }}">
                            @error('company')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="partner_message">Message</label>
                            <textarea class="site-form__textarea" id="partner_message" name="message" placeholder="Tell us about your business and partnership goals" required>{{ old('message') }}</textarea>
                            @error('message')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <button type="submit" class="site-btn site-btn--primary">Submit application</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('partner_type');
    const applyButtons = document.querySelectorAll('.partner-apply-btn');

    applyButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const type = btn.getAttribute('data-partner-type');
            if (typeSelect && type) {
                typeSelect.value = type;
            }
        });
    });

    const params = new URLSearchParams(window.location.search);
    const typeFromQuery = params.get('type');
    if (typeSelect && typeFromQuery && !typeSelect.value) {
        typeSelect.value = typeFromQuery;
    }
})();
</script>
@endpush
