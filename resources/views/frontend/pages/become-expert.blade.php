@extends('frontend.layouts.tavelo')

@section('title', 'Become an Expert — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-hero', [
        'kicker' => 'Partnerships',
        'title' => 'Become a Tavelo travel expert',
        'text' => 'Join our network of specialists helping travelers book smarter flights and build richer itineraries.',
        'image' => asset('assets/img/hero/hero-3.jpg'),
        'primaryLabel' => 'Apply now',
        'primaryUrl' => '#expert-apply',
        'secondaryLabel' => 'Learn about us',
        'secondaryUrl' => route('pages.about'),
        'current' => 'Become an Expert',
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker">Why join</span>
                <h2 class="site-heading">Grow with a modern travel platform</h2>
                <p class="site-lead">Whether you are an independent consultant or agency lead, Tavelo gives you tools and visibility to support more travelers.</p>
            </div>
            <div class="site-grid site-grid--3">
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-chart-line"></i></span>
                    <div>
                        <h3 class="site-feature__title">Expand your reach</h3>
                        <p class="site-feature__text">Get discovered by travelers looking for trusted guidance on complex trips.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-suitcase-rolling"></i></span>
                    <div>
                        <h3 class="site-feature__title">Flight-first toolkit</h3>
                        <p class="site-feature__text">Work with a clean search and booking experience designed for professional use.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-award"></i></span>
                    <div>
                        <h3 class="site-feature__title">Trusted brand fit</h3>
                        <p class="site-feature__text">Align with a polished navy-and-gold identity that feels premium and professional.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface" id="expert-apply">
        <div class="site-container">
            @if(session('success'))
                <div class="alert alert-success home-flight-alert" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
            @endif

            <div class="site-grid site-grid--2">
                <div>
                    <span class="site-kicker">Application</span>
                    <h2 class="site-heading">Tell us about your expertise</h2>
                    <p class="site-lead" style="margin-bottom: 1rem;">Share your background and preferred destinations. Our partnerships team reviews every application carefully.</p>
                    <ul class="site-contact-list">
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>Independent consultants welcome</strong>
                                <span>Build your personal travel practice with us.</span>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>Agencies &amp; teams</strong>
                                <span>Bring your desk and grow under one brand system.</span>
                            </div>
                        </li>
                        <li>
                            <i class="far fa-check"></i>
                            <div>
                                <strong>Destination specialists</strong>
                                <span>Highlight niche expertise travelers are searching for.</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="site-panel">
                    <form class="site-form" method="POST" action="{{ route('pages.become-expert.submit') }}">
                        @csrf
                        <div class="site-form__group">
                            <label class="site-form__label" for="expert_name">Full name</label>
                            <input class="site-form__input" id="expert_name" type="text" name="full_name" value="{{ old('full_name') }}" required>
                            @error('full_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="site-form__row">
                            <div class="site-form__group">
                                <label class="site-form__label" for="expert_email">Email</label>
                                <input class="site-form__input" id="expert_email" type="email" name="email" value="{{ old('email') }}" required>
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="site-form__group">
                                <label class="site-form__label" for="expert_phone">Phone</label>
                                <input class="site-form__input" id="expert_phone" type="text" name="phone" value="{{ old('phone') }}">
                                @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="site-form__row">
                            <div class="site-form__group">
                                <label class="site-form__label" for="expert_expertise">Expertise</label>
                                <input class="site-form__input" id="expert_expertise" type="text" name="expertise" placeholder="e.g. Europe flights" value="{{ old('expertise') }}" required>
                                @error('expertise')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="site-form__group">
                                <label class="site-form__label" for="expert_experience">Experience</label>
                                <select class="site-form__input" id="expert_experience" name="experience">
                                    <option value="">Select</option>
                                    <option value="0-1" @selected(old('experience') === '0-1')>0–1 years</option>
                                    <option value="1-3" @selected(old('experience') === '1-3')>1–3 years</option>
                                    <option value="3-5" @selected(old('experience') === '3-5')>3–5 years</option>
                                    <option value="5+" @selected(old('experience') === '5+')>5+ years</option>
                                </select>
                                @error('experience')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="site-form__group">
                            <label class="site-form__label" for="expert_message">About you</label>
                            <textarea class="site-form__textarea" id="expert_message" name="message" placeholder="Tell us about your destinations, clients, and goals">{{ old('message') }}</textarea>
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
