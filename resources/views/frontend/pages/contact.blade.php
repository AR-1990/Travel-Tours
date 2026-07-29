@extends('frontend.layouts.tavelo')

@section('title', 'Contact — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'Contact',
        'current' => 'Contact',
        'kicker' => 'Support',
        'text' => 'Ask about flights, itineraries, partnerships, or upcoming hotel and activity options.',
        'image' => asset('assets/img/hero/hero-2.jpg'),
    ])

    <section class="site-section">
        <div class="site-container">
            @if(session('success'))
                <div class="alert alert-success home-flight-alert" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
            @endif

            <div class="site-grid site-grid--2">
                <div class="site-panel">
                    <span class="site-kicker"><i class="far fa-envelopes"></i> Get in touch</span>
                    <h2 class="site-heading">We would love to hear from you</h2>
                    <p class="site-lead" style="margin-bottom: 1.5rem;">Ask about flights, itineraries, partnerships, or upcoming hotel and activity options.</p>

                    <ul class="site-contact-list">
                        <li>
                            <i class="far fa-phone"></i>
                            <div>
                                <strong>Phone</strong>
                                <a href="tel:+21234567897">+2 123 4567 897</a>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <a href="mailto:info@wisetrust.com">info@wisetrust.com</a>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-location-dot"></i>
                            <div>
                                <strong>Office</strong>
                                <span>Travel Hub, Business District</span>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-clock"></i>
                            <div>
                                <strong>Hours</strong>
                                <span>Mon–Sat, 9:00 AM – 8:00 PM</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="site-panel">
                    <h3 class="site-heading" style="font-size:1.4rem;">Send a message</h3>
                    <form class="site-form" method="POST" action="{{ route('pages.contact.submit') }}">
                        @csrf
                        <div class="site-form__row">
                            <div class="site-form__group">
                                <label class="site-form__label" for="contact_name">Name</label>
                                <input class="site-form__input" id="contact_name" type="text" name="name" value="{{ old('name') }}" required>
                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="site-form__group">
                                <label class="site-form__label" for="contact_email">Email</label>
                                <input class="site-form__input" id="contact_email" type="email" name="email" value="{{ old('email') }}" required>
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="contact_subject">Subject</label>
                            <input class="site-form__input" id="contact_subject" type="text" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="contact_message">Message</label>
                            <textarea class="site-form__textarea" id="contact_message" name="message" required>{{ old('message') }}</textarea>
                            @error('message')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <button type="submit" class="site-btn site-btn--primary">Send message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
