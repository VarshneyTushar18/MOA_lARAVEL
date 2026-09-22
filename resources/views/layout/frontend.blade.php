<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title ?? 'Website' }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<!-- GLightbox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}?v=20260922profiledesktop">
    @stack('head')
</head>

<body>

    <header>
        <div class="container-fluid header-container">
            <div class="header-brand-row py-2">
                <div class="header-brand-left">
                    <a href="/">
                        <img src="{{ asset('assets/images/ministry-ayush-logo.png') }}?v=20260921moalogo" alt="Government of India, Ministry of Ayush" class="ministry-logo">
                    </a>
                </div>
                <div class="header-brand-right">
                    <a href="/">
                        <img src="{{ asset('assets/images/aiia-header-logo.png') }}?v=20260922aiiaone" alt="All India Institute of Ayurveda" class="aiia-header-logo">
                    </a>
                </div>
            </div>

            <div class="row align-items-center header-search-row pb-2">
                <div class="col-12">
                    <div class="search-block">
                        <form action="{{ route('patient.search') }}" method="GET">
                            <input type="text" name="q" placeholder="Search" minlength="2" maxlength="255" value="{{ old('q', request('q')) }}" title="At least 2 characters">
                            <button type="submit">
                                <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row pt-2">
                <div class="col-12">
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
                                    <a class="nav-link" href="/performance_report">Performance Report</a>
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

    <main class="page-content">
        @yield('content')
    </main>

    @include('partials.site-footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    @stack('scripts')
</body>

</html>