<footer class="footer-compact">
    <div class="container pb-4">
        <div class="row footer-main-row g-3 g-lg-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="footer-widget">
                    @php
                        $logoUrl = strtolower($siteFooter['logo']['url'] ?? '');
                        $footerLogoWide = str_contains($logoUrl, 'main-logo')
                            || str_contains($logoUrl, 'ministry-ayush')
                            || str_contains($logoUrl, '/nam.')
                            || str_contains($logoUrl, 'nam.webp');
                    @endphp
                    <div class="footer-logo {{ $footerLogoWide ? 'footer-logo--wide' : 'footer-logo--circle' }}">
                        <a href="/">
                            <img src="{{ $siteFooter['logo']['url'] }}" alt="{{ $siteFooter['logo']['alt'] }}" class="footer-logo__image">
                        </a>
                    </div>

                    @if(!empty($siteFooter['sponsors']))
                        <div class="sponsors-logos d-flex align-items-center gap-2 w-100">
                            @foreach($siteFooter['sponsors'] as $sponsor)
                                @php
                                    $sponsorUrl = strtolower($sponsor['url'] ?? '');
                                    $sponsorAlt = strtolower($sponsor['alt'] ?? '');
                                    $sponsorRound = str_contains($sponsorUrl, 'aiia')
                                        || str_contains($sponsorUrl, 'pm-yojna')
                                        || str_contains($sponsorUrl, 'pm_yojna')
                                        || str_contains($sponsorAlt, 'aiia');
                                    $sponsorClass = $sponsorRound
                                        ? ''
                                        : ' sponsors-logos__image--ministry-wide';
                                @endphp
                                <img src="{{ $sponsor['url'] }}" alt="{{ $sponsor['alt'] ?? '' }}" class="sponsors-logos__image{{ $sponsorClass }}">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="footer-widget">
                    <h4>{{ $siteFooter['useful_links']['title'] }}</h4>
                    <ul>
                        @foreach($siteFooter['useful_links']['items'] as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="footer-widget">
                    <h4>{{ $siteFooter['important_links']['title'] }}</h4>
                    <ul>
                        @foreach($siteFooter['important_links']['items'] as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="footer-widget text-white">
                    <h4>{{ $siteFooter['contact']['title'] }}</h4>
                    <div class="contact-address pb-2 d-flex gap-2">
                        <div class="icon">
                            <i class="ri-map-pin-line"></i>
                        </div>
                        <div class="context">
                            {!! nl2br(e($siteFooter['contact']['address'])) !!}
                        </div>
                    </div>
                    @if(filled($siteFooter['contact']['phone']))
                        <div class="contact-phone pb-2 d-flex gap-2 align-items-center">
                            <div class="icon">
                                <i class="ri-phone-line"></i>
                            </div>
                            <div class="context">
                                {{ $siteFooter['contact']['phone'] }}
                            </div>
                        </div>
                    @endif
                    @if(filled($siteFooter['contact']['email']))
                        <div class="contact-mail pb-2 d-flex gap-2 align-items-center">
                            <div class="icon">
                                <i class="ri-mail-send-line"></i>
                            </div>
                            <div class="context">
                                {{ $siteFooter['contact']['email'] }}
                            </div>
                        </div>
                    @endif

                    @if(filled($siteFooter['contact']['map_embed'] ?? null))
                        <div class="footer-map pt-1">
                            <div class="map-block">
                                {!! $siteFooter['contact']['map_embed'] !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom text-white">
        <div class="container">
            <div class="row footer-bottom-row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <div class="copyright">
                        <p class="mb-0">{{ $siteFooter['bottom']['copyright'] }}</p>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    @if(!empty($siteFooter['bottom']['legal_links']))
                        <ul class="legal-links">
                            @foreach($siteFooter['bottom']['legal_links'] as $link)
                                <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
