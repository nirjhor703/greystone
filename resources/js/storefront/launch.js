document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('.store-poster-carousel')
        .forEach((carousel) => {
            const slider = carousel.querySelector('[data-offer-slider]');
            const nextButton = carousel.querySelector('[data-offer-next]');
            let autoSlideTimer = null;
            let isInViewport = true;
            const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

            if (!slider) {
                return;
            }

            const goNext = () => {
                const firstCard = slider.querySelector('.store-poster-card');

                if (!firstCard) {
                    return;
                }

                const gap = Number.parseFloat(
                    window.getComputedStyle(slider).columnGap
                    || window.getComputedStyle(slider).gap
                    || 0
                );
                const step = firstCard.getBoundingClientRect().width + gap;
                const maxScroll = slider.scrollWidth - slider.clientWidth;
                const isAtEnd = slider.scrollLeft >= maxScroll - 8;
                const nextScroll = Math.min(slider.scrollLeft + step, maxScroll);

                slider.scrollTo({
                    left: isAtEnd ? 0 : nextScroll,
                    behavior: 'smooth',
                });
            };

            nextButton?.addEventListener('click', goNext);

            const stopAutoSlide = () => {
                window.clearInterval(autoSlideTimer);
                autoSlideTimer = null;
            };

            const startAutoSlide = () => {
                stopAutoSlide();
                if (!prefersReducedMotion && isInViewport && !document.hidden && slider.querySelectorAll('.store-poster-card').length > 1) {
                    autoSlideTimer = window.setInterval(goNext, 3000);
                }
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    isInViewport = entries[0]?.isIntersecting ?? true;
                    if (isInViewport) {
                        startAutoSlide();
                    } else {
                        stopAutoSlide();
                    }
                }, { rootMargin: '120px 0px' });
                observer.observe(slider);
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAutoSlide();
                } else {
                    startAutoSlide();
                }
            });

            if (!prefersReducedMotion) {
                startAutoSlide();
            }
        });
});
