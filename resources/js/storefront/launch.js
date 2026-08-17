document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('.store-poster-carousel')
        .forEach((carousel) => {
            const slider = carousel.querySelector('[data-offer-slider]');
            const nextButton = carousel.querySelector('[data-offer-next]');

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

            if (slider.querySelectorAll('.store-poster-card').length > 1) {
                window.setInterval(goNext, 3000);
            }
        });
});
