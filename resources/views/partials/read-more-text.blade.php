@php
    $limit = (int) ($limit ?? 50);
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
    $wordList = $plain === '' ? [] : preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
    $needsToggle = count($wordList) > $limit;
    $excerpt = $needsToggle ? implode(' ', array_slice($wordList, 0, $limit)) . '…' : $plain;
@endphp

<div class="read-more-text {{ $class ?? '' }}">
    @if($needsToggle)
        <div class="read-more-text__excerpt">{!! nl2br(e($excerpt)) !!}</div>
        @if(!empty($readMoreUrl))
            <a href="{{ $readMoreUrl }}" class="yojna-read-more">Read More</a>
        @else
            <div class="read-more-text__full d-none">{!! nl2br(e($text)) !!}</div>
            <button type="button" class="read-more-toggle" aria-expanded="false">
                <span class="read-more-toggle__more">Read More</span>
                <span class="read-more-toggle__less d-none">Read Less</span>
            </button>
        @endif
    @elseif(!empty($readMoreUrl) && filled($plain))
        {!! nl2br(e($plain)) !!}
        <a href="{{ $readMoreUrl }}" class="yojna-read-more">Read More</a>
    @else
        {!! nl2br(e($text)) !!}
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.read-more-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var wrap = button.closest('.read-more-text');
                        if (!wrap) return;

                        var excerpt = wrap.querySelector('.read-more-text__excerpt');
                        var full = wrap.querySelector('.read-more-text__full');
                        var more = button.querySelector('.read-more-toggle__more');
                        var less = button.querySelector('.read-more-toggle__less');
                        var expanded = button.getAttribute('aria-expanded') === 'true';

                        if (expanded) {
                            full.classList.add('d-none');
                            excerpt.classList.remove('d-none');
                            more.classList.remove('d-none');
                            less.classList.add('d-none');
                            button.setAttribute('aria-expanded', 'false');
                        } else {
                            full.classList.remove('d-none');
                            excerpt.classList.add('d-none');
                            more.classList.add('d-none');
                            less.classList.remove('d-none');
                            button.setAttribute('aria-expanded', 'true');
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
