<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title ?? 'Website' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<!-- GLightbox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    @stack('head')
</head>

<body>

    <header>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="social-media">
                        <ul>
                            <li><a href="#"><img src="{{ asset('assets/images/facebook.svg') }}" alt="Facebook"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/linkedin.svg') }}" alt="Linkedin"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/instagram.svg') }}" alt="Instagram"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/twitter-x.svg') }}" alt="Twitter X"></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="logo-block">
                        <a href="/">
                            <img src="{{ asset('assets/images/Main-logo.png') }}" alt="Logo" class="img-fluid">
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="search-block">
                        <form action="{{ route('patient.search') }}" method="GET">
                            <input type="text" name="q" placeholder="Search" value="{{ request('q') }}">
                            <button type="submit">
                                <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row pt-2">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <nav class="navbar navbar-expand-lg">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNavDropdown">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" aria-current="page" href="/">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/about">About us</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/factsheet">Fact Sheet</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/acsm_iec">ACSM / IEC</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/performance_report">Performance report</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/best_practices">Best Practices</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/patient_corner">Patient Corner</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/contact">Contact us</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    @yield('content')

    <footer>
        <div class="container pb-5">
            <div class="row pb-3">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a href="#">
                                <img src="assets/images/Main-logo.png" alt="Ministry of Ayush" class="img-fluid">
                            </a>
                        </div>

                        <div class="sponsors-logos d-flex align-items-center gap-1 w-100">
                            <img src="assets/images/aiia.webp" alt="" class="img-fluid">
                            <img src="assets/images/pm-yojna.webp" alt="" class="img-fluid">
                            <img src="assets/images/nam.webp" alt="Ministry of Ayush" class="img-fluid">
                        </div>

                        <div class="social-media">
                            <ul>
                                <li><a href="#"><img src="assets/images/facebook.svg" alt="Facebook"></a></li>
                                <li><a href="#"><img src="assets/images/linkedin.svg" alt="Linkedin"></a></li>
                                <li><a href="#"><img src="assets/images/instagram.svg" alt="Instagram"></a></li>
                                <li><a href="#"><img src="assets/images/twitter-x.svg" alt="Twitter X"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-widget">
                        <h4>Useful Links</h4>
                        <ul>
                            <li><a href="/about">About us</a></li>
                            <li><a href="/factsheet">Fact Sheet</a></li>
                            <li><a href="/acsm_iec">ACSM / IEC</a></li>
                            <li><a href="/performance_report">Performance report</a></li>
                            <li><a href="/best_practices">Best Practices</a></li>
                            <li><a href="/patient_corner">Patient Corner</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-widget">
                        <h4>Important Links</h4>
                        <ul>
                            <li><a href="#">National Ayush Mission (NAM)</a></li>
                            <li><a href="#">Central Sector Schemes</a></li>
                            <li><a href="#">Public Grievances</a></li>
                            <li><a href="#">Explore What's new</a></li>
                            <li><a href="#">Explore Press Release</a></li>
                            <li><a href="#">Explore Vacancy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-widget text-white">
                        <h4>Contact Information</h4>
                        <div class="contact-address pb-3 d-flex gap-3">
                            <div class="icon">
                                <i class="ri-map-pin-line"></i>
                            </div>
                            <div class="context">
                                All India Institute of Ayurveda (AIIA) Mathura Road, Gautam Puri
                                Sarita Vihar, Delhi - 110076
                            </div>
                        </div>
                        <div class="contact-phone pb-3 d-flex gap-3 align-items-center">
                            <div class="icon">
                                <i class="ri-phone-line"></i>
                            </div>
                            <div class="context">
                                Phone No : 011-26950401/402
                            </div>
                        </div>
                        <div class="contact-mail pb-3 d-flex gap-3 align-items-center">
                            <div class="icon">
                                <i class="ri-mail-send-line"></i>
                            </div>
                            <div class="context">
                                Email Id : contact-us@aiia.gov.in
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-white">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="copyright">
                            <p class="mb-0">© Copyright 2026 Ministry of Ayush. All Rights Reserved</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <ul class="legal-links">
                            <li><a href="#">Terms & Conditions</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    @stack('scripts')
</body>

</html>