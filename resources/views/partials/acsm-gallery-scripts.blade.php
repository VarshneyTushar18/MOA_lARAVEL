<script>
function pauseAcsmVideos(container) {
    if (!container) {
        return;
    }
    container.querySelectorAll('video').forEach(function (video) {
        video.pause();
    });
}

function initAcsmVideoGallery(wrap) {
    if (!wrap || wrap.dataset.swiperReady === '1' || !wrap.classList.contains('acsm-video-gallery--carousel')) {
        return;
    }
    var el = wrap.querySelector('.workshop-video-swiper');
    if (!el || typeof Swiper === 'undefined') {
        return;
    }

    var slideCount = parseInt(wrap.getAttribute('data-slide-count') || '0', 10);
    if (!slideCount) {
        slideCount = el.querySelectorAll('.swiper-slide').length;
    }

    var paginationEl = wrap.querySelector('.workshop-video-gallery__fraction');

    new Swiper(el, {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: slideCount > 1,
        speed: 400,
        observer: true,
        observeParents: true,
        pagination: paginationEl ? {
            el: paginationEl,
            type: 'fraction',
            formatFractionCurrent: function (n) { return n; },
            formatFractionTotal: function (n) { return n; }
        } : false,
        navigation: {
            nextEl: wrap.querySelector('.workshop-video-gallery__btn--next'),
            prevEl: wrap.querySelector('.workshop-video-gallery__btn--prev'),
        },
        on: {
            slideChange: function () {
                pauseAcsmVideos(wrap);
            }
        }
    });

    wrap.dataset.swiperReady = '1';
}

function initAcsmImageGallery(wrap) {
    if (!wrap || wrap.dataset.swiperReady === '1' || !wrap.classList.contains('acsm-image-gallery--carousel')) {
        return;
    }
    var el = wrap.querySelector('.acsm-image-swiper');
    if (!el || typeof Swiper === 'undefined') {
        return;
    }

    var slideCount = parseInt(wrap.getAttribute('data-slide-count') || '0', 10);
    if (!slideCount) {
        slideCount = el.querySelectorAll('.swiper-slide').length;
    }

    var paginationEl = wrap.querySelector('.workshop-gallery__fraction');

    var swiper = new Swiper(el, {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: slideCount > 1 && slideCount <= 24,
        speed: 400,
        observer: true,
        observeParents: true,
        watchOverflow: true,
        pagination: paginationEl ? {
            el: paginationEl,
            type: 'fraction',
            formatFractionCurrent: function (n) { return n; },
            formatFractionTotal: function (n) { return n; }
        } : false,
        navigation: {
            nextEl: wrap.querySelector('.workshop-gallery__btn--next'),
            prevEl: wrap.querySelector('.workshop-gallery__btn--prev'),
        }
    });

    function refreshImageSwiper() {
        swiper.update();
    }

    wrap.querySelectorAll('.workshop-gallery__img').forEach(function (img) {
        if (img.complete) {
            return;
        }
        img.addEventListener('load', refreshImageSwiper, { once: true });
        img.addEventListener('error', refreshImageSwiper, { once: true });
    });

    refreshImageSwiper();
    window.requestAnimationFrame(refreshImageSwiper);

    wrap.dataset.swiperReady = '1';
}

function initAcsmCarouselsIn(container) {
    if (!container) {
        return;
    }
    container.querySelectorAll('.acsm-image-gallery--carousel').forEach(initAcsmImageGallery);
    container.querySelectorAll('.acsm-video-gallery--carousel').forEach(function (wrap) {
        initAcsmVideoGallery(wrap);
        var swiperEl = wrap.querySelector('.workshop-video-swiper');
        if (swiperEl && swiperEl.swiper) {
            swiperEl.swiper.update();
        }
    });
}
</script>
