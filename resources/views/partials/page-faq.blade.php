@php
    $groupedSections = isset($grouped)
        ? $grouped
        : (($page->sections ?? collect())->groupBy('section_key'));

    $faqKeys = ['faq', 'faqs', 'frequently_asked_questions'];
    $faqSection = collect($faqKeys)->map(function ($key) use ($groupedSections) {
        return $groupedSections->get($key, collect())->first();
    })->first(function ($section) {
        return !is_null($section);
    });

    $faqItems = $faqSection
        ? $faqSection->subsections->sortBy('sort_order')->values()
        : collect();

    $faqId = 'faq-' . ($page->slug ?? uniqid());
@endphp

@if($faqSection && $faqItems->count())
<section class="ntpcsection mt-5">
    <div class="container faq-ui" id="{{ $faqId }}">
        <div class="faq-ui__head text-center">
            <h2>{{ $faqSection->title ?: 'Frequently Asked Questions' }}</h2>
        </div>

        <div class="faq-ui__search-wrap">
            <input type="search" class="form-control faq-ui__search js-faq-search" placeholder="Search here...">
            <i class="ri-search-line faq-ui__search-icon" aria-hidden="true"></i>
        </div>

        <div class="accordion faq-ui__accordion" id="{{ $faqId }}-accordion">
            @foreach($faqItems as $index => $item)
                @php
                    $headingId = $faqId . '-heading-' . $index;
                    $collapseId = $faqId . '-collapse-' . $index;
                @endphp
                <div class="accordion-item faq-ui__item js-faq-item"
                     data-faq-text="{{ strtolower(trim(($item->title ?? '') . ' ' . strip_tags($item->description ?? ''))) }}">
                    <h2 class="accordion-header" id="{{ $headingId }}">
                        <button class="accordion-button collapsed faq-ui__button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false"
                                aria-controls="{{ $collapseId }}">
                            {{ $item->title }}
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}"
                         class="accordion-collapse collapse"
                         aria-labelledby="{{ $headingId }}"
                         data-bs-parent="#{{ $faqId }}-accordion">
                        <div class="accordion-body faq-ui__body">
                            {!! nl2br(e($item->description ?? '')) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var panels = document.querySelectorAll('.faq-ui');
    panels.forEach(function (panel) {
        var search = panel.querySelector('.js-faq-search');
        var items = panel.querySelectorAll('.js-faq-item');

        if (!search) return;

        search.addEventListener('input', function (event) {
            var query = (event.target.value || '').toLowerCase().trim();
            items.forEach(function (item) {
                var txt = item.getAttribute('data-faq-text') || '';
                item.style.display = (!query || txt.indexOf(query) !== -1) ? '' : 'none';
            });
        });
    });
});
</script>
@endpush
@endif
