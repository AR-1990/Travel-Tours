    <footer class="footer-area ft-bg">
        <div class="footer-widget pt-60">
            <div class="container">
                <div class="row footer-widget-wrapper pt-100 pb-70">
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box about-us">
                            <a href="{{ route('home') }}" class="footer-logo">
                                <img src="{{ asset('assets/img/logo/logo.png') }}" alt="">
                            </a>
                            <p class="mb-4">
                                We are many variations of passages available but the majority have suffer alteration
                                in some form by injected.
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
                                <li><i class="far fa-map-marker-alt"></i>Office#204, Kababjees Fried Chicken Building, Crown square, adjacent to Usmania restaurant, Block 13 A Gulshan-e-Iqbal, Karachi, 76800</li>
                                <li><a href="mailto:info@wisetrusttravel.com"><i
                                            class="far fa-envelopes"></i><span>info@wisetrusttravel.com</span></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Our Company</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> About Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Meet Our Team</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Contact Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Affiliate Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Advertising With Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Careers</a></li>
                                <li><a href="{{ route('blogs.index') }}"><i class="fas fa-angle-double-right"></i> Our Blog</a></li>

                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Other Services</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Rewards Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Partners</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Community Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Investor Relations</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Developer Guide</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Travel API</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> PointsPLUS</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Help Center</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Account</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> FAQ's</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Legal Notice</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Privacy Policy</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Terms & Conditions</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Live Chat</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Sitemap</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Newsletter</h4>
                            <div class="footer-newsletter">
                                <p>Subscribe Our Newsletter To Get Latest Update And News</p>
                                <div class="subscribe-form">
                                    <form action="#">
                                        <div class="form-group">
                                            <div class="form-group-icon">
                                                <input type="email" class="form-control" placeholder="Your Email">
                                                <i class="far fa-envelopes"></i>
                                            </div>
                                        </div>
                                        <button class="theme-btn" type="submit">
                                            Subscribe Now <i class="far fa-paper-plane"></i>
                                        </button>
                                        <p><i class="far fa-lock"></i> Your information is safe with us.</p>
                                    </form>
                                </div>
                            </div>
                            <div class="footer-payment-method">
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
                            &copy; Copyright <span id="date"></span> <a href="#"> Tavelo </a> All Rights Reserved.
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
