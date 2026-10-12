    <footer class="footer-area ft-bg footer-area--compact">
        <div class="footer-widget">
            <div class="container">
                <div class="row footer-widget-wrapper g-4">
                    <div class="col-md-6 col-lg-5">
                        <div class="footer-widget-box about-us">
                            <a href="{{ route('home') }}" class="footer-logo">
                                <img src="{{ asset('assets/img/logo/logo.png') }}" alt="{{ config('app.name') }}">
                            </a>
                            <p class="mb-3">
                                Wise Trust Travel &amp; Tourism — your trusted partner for flights, stays, and journeys.
                            </p>
                            <ul class="footer-contact">
                                <li>
                                    <div class="footer-call">
                                        <div class="footer-call-icon">
                                            <i class="fal fa-headset"></i>
                                        </div>
                                        <div class="footer-call-info">
                                            <h6>Call Service</h6>
                                            <a href="tel:+923162295519">+92 316 2295519</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <i class="far fa-map-marker-alt"></i>
                                    Office#204, Kababjees Fried Chicken Building, Crown square, Block 13 A Gulshan-e-Iqbal, Karachi
                                </li>
                                <li>
                                    <a href="mailto:info@wisetrusttravel.com">
                                        <i class="far fa-envelopes"></i>
                                        <span>info@wisetrusttravel.com</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 col-lg-3">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Our Company</h4>
                            <ul class="footer-list">
                                <li><a href="{{ route('pages.about') }}"><i class="fas fa-angle-double-right"></i> About Us</a></li>
                                <li><a href="{{ route('pages.contact') }}"><i class="fas fa-angle-double-right"></i> Contact Us</a></li>
                                <li><a href="{{ route('pages.partner-with-us') }}"><i class="fas fa-angle-double-right"></i> Partner With Us</a></li>
                                <li><a href="{{ route('blogs.index') }}"><i class="fas fa-angle-double-right"></i> Our Blog</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 col-lg-4">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Live Chat</h4>
                            <p class="mb-3">Need help with flights or bookings? Reach us during working hours.</p>
                            <p class="mb-3 footer-hours"><i class="far fa-clock"></i> 10:00 AM – 06:00 PM</p>
                            <a href="{{ route('pages.contact') }}" class="theme-btn">Start Live Chat <i class="far fa-comments"></i></a>
                            <div class="footer-payment-method mt-3">
                                <h6>We Accept:</h6>
                                <div class="payment-method-img">
                                    <img src="{{ asset('assets/img/payment/paypal.svg') }}" alt="">
                                    <img src="{{ asset('assets/img/payment/mastercard.svg') }}" alt="">
                                    <img src="{{ asset('assets/img/payment/visa.svg') }}" alt="">
                                    <img src="{{ asset('assets/img/payment/discover.svg') }}" alt="">
                                    <img src="{{ asset('assets/img/payment/american-express.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 align-self-center">
                        <p class="copyright-text">
                            &copy; Copyright <span id="date"></span>
                            <a href="{{ route('home') }}">{{ config('app.name') }}</a>
                            All Rights Reserved.
                        </p>
                    </div>
                    <div class="col-md-6 align-self-center">
                        <ul class="footer-social">
                            <li><a href="https://www.facebook.com/share/1HjnhgKLtm/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="https://x.com/wisetrusttrav?s=11" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a></li>
                            <li><a href="https://www.instagram.com/wisetrusttravelandtourismkhi?utm_source=qr" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="https://www.tiktok.com/@wisetrusttravel.a" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
