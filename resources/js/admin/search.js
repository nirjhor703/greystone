document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('.admin-ajax-search')
        .forEach((form) => {
            const targetSelector = form.dataset.target;
            const target = document.querySelector(targetSelector);

            if (!target) {
                return;
            }

            let timer = null;
            let controller = null;

            function queryUrl(url = form.getAttribute('action')) {
                const params = new URLSearchParams(new FormData(form));

                Array.from(params.entries()).forEach(([key, value]) => {
                    if (!value) {
                        params.delete(key);
                    }
                });

                return `${url}?${params.toString()}`;
            }

            async function runSearch(url = null) {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();
                form.classList.add('is-loading');

                try {
                    const response = await fetch(url || queryUrl(), {
                        headers: {
                            Accept: 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Search failed.');
                    }

                    target.innerHTML = await response.text();
                    window.history.replaceState({}, '', url || queryUrl());
                    document.dispatchEvent(
                        new CustomEvent('admin-search:updated', {
                            detail: {
                                target,
                                form,
                            },
                        })
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        target.innerHTML = `
                            <div class="brand-empty-state">
                                <strong>Search failed</strong>
                                <span>Please try again.</span>
                            </div>
                        `;
                    }
                } finally {
                    form.classList.remove('is-loading');
                }
            }

            function scheduleSearch() {
                clearTimeout(timer);
                timer = setTimeout(runSearch, 280);
            }

            form.addEventListener('input', scheduleSearch);
            form.addEventListener('change', runSearch);

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                runSearch();
            });

            form.addEventListener('reset', () => {
                setTimeout(runSearch, 0);
            });

            target.addEventListener('click', (event) => {
                const link = event.target.closest('.pagination a');

                if (!link) {
                    return;
                }

                event.preventDefault();
                runSearch(link.href);
            });
        });
});
