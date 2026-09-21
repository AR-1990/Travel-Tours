<script>
(function () {
    if (window.__adminAjaxFiltersInit) return;
    window.__adminAjaxFiltersInit = true;

    function debounce(fn, wait) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    async function loadFilter(form, url) {
        const results = document.querySelector(form.dataset.results || '#js-ajax-filter-results');
        if (!results) return;

        results.classList.add('ajax-filter-busy');
        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('Filter request failed');
            const html = await res.text();
            results.innerHTML = html;
            history.replaceState({}, '', url);
        } catch (_err) {
            window.location.href = url;
        } finally {
            results.classList.remove('ajax-filter-busy');
        }
    }

    function formUrl(form) {
        const action = form.getAttribute('action') || window.location.pathname;
        const params = new URLSearchParams(new FormData(form));
        // Drop empty filter values so URLs stay clean.
        [...params.keys()].forEach((key) => {
            if ((params.get(key) || '').trim() === '') {
                params.delete(key);
            }
        });
        const query = params.toString();
        return query ? `${action}?${query}` : action;
    }

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-ajax-filter-form')) {
            return;
        }
        e.preventDefault();
        loadFilter(form, formUrl(form));
    });

    document.addEventListener('change', function (e) {
        const el = e.target;
        if (!(el instanceof HTMLElement)) return;
        const form = el.closest('form.js-ajax-filter-form');
        if (!form) return;
        if (el.matches('select, input[type="checkbox"], input[type="radio"]')) {
            loadFilter(form, formUrl(form));
        }
    });

    const debouncedSearch = debounce(function (form) {
        loadFilter(form, formUrl(form));
    }, 350);

    document.addEventListener('input', function (e) {
        const el = e.target;
        if (!(el instanceof HTMLInputElement)) return;
        const form = el.closest('form.js-ajax-filter-form');
        if (!form || el.type !== 'text' && el.type !== 'search') return;
        debouncedSearch(form);
    });

    document.addEventListener('click', function (e) {
        const link = e.target.closest('.js-ajax-filter-pager a');
        if (!link) return;
        const results = link.closest('#js-ajax-filter-results, [data-ajax-filter-results]');
        const form = document.querySelector('form.js-ajax-filter-form');
        if (!results || !form) return;
        e.preventDefault();
        loadFilter(form, link.href);
    });
})();
</script>
<style>
.ajax-filter-busy {
    opacity: 0.55;
    pointer-events: none;
    position: relative;
}
.ajax-filter-busy::after {
    content: '';
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 1.1rem;
    height: 1.1rem;
    border: 2px solid #6c757d;
    border-right-color: transparent;
    border-radius: 50%;
    animation: ajax-filter-spin 0.7s linear infinite;
}
@keyframes ajax-filter-spin {
    to { transform: rotate(360deg); }
}
</style>
