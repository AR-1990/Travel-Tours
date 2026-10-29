<script>
(function () {
    if (window.__flightsAjaxInit) return;
    window.__flightsAjaxInit = true;
    let busy = false;
    let busyButtons = [];

    function setBusy(nextBusy, targetForm = null) {
        busy = nextBusy;
        if (nextBusy) {
            const scope = targetForm instanceof HTMLFormElement ? targetForm : null;
            const buttons = scope
                ? Array.from(scope.querySelectorAll('button[type="submit"]'))
                : [];
            busyButtons = buttons.filter(btn => btn instanceof HTMLButtonElement);
            busyButtons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('flights-btn-loading');
            });
        } else {
            busyButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('flights-btn-loading');
            });
            busyButtons = [];
        }
        document.body.classList.toggle('flights-ajax-busy', nextBusy);
    }

    function isFlightsLink(link) {
        if (!link) return false;
        if (link.target === '_blank' || link.hasAttribute('download')) return false;
        const href = link.getAttribute('href') || '';
        if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;
        const url = new URL(link.href, window.location.origin);
        return url.origin === window.location.origin && url.pathname.includes('/flights');
    }

    function scrollToFlightsResults() {
        const target = document.querySelector(
            '.flights-page [aria-label="Search results"], .flights-page .results-header, .flights-page .empty-results, .flights-page'
        );
        if (!target) return;

        const nav = document.querySelector('.main-navigation, .navbar, .main-header');
        const offset = (nav ? nav.offsetHeight : 72) + 20;
        const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    async function swapFlightsPageFromHtml(html, nextUrl) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newPage = doc.querySelector('.flights-page');
        const currentPage = document.querySelector('.flights-page');
        if (!newPage || !currentPage) {
            // Layout without .flights-page (redirect HTML) — hard navigate.
            if (nextUrl) {
                window.location.href = nextUrl;
            }
            return;
        }
        currentPage.replaceWith(newPage);
        // DOM swap drops AirportPicker instances — re-bind before the next search.
        window.initAirportPickers?.();
        window.initFlightUi?.();
        window.initFlightProviderAirportScopes?.();
        window.initFlightResultFilters?.();
        if (nextUrl) {
            history.pushState({}, '', nextUrl);
        }
        window.setTimeout(scrollToFlightsResults, 50);
    }

    document.addEventListener('click', async function (e) {
        if (busy) return;
        const link = e.target.closest('a');
        if (!isFlightsLink(link)) return;
        e.preventDefault();
        try {
            setBusy(true);
            const res = await fetch(link.href, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const html = await res.text();
            await swapFlightsPageFromHtml(html, link.href);
        } catch (_err) {
            window.location.href = link.href;
        } finally {
            setBusy(false);
        }
    });

    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.closest('.flights-page')) return;

        const method = (form.method || 'GET').toUpperCase();
        if (method !== 'POST') return;
        if (busy) return;

        // keep native for non-flight forms inside same page if explicitly requested
        if (form.hasAttribute('data-no-ajax')) return;

        // search form validation
        if (form.id === 'flightSearchForm') {
            const trip = form.querySelector('input[name="trip_type"]:checked')?.value || 'oneway';
            if (trip !== 'multicity') {
                const o = window.getAirportPicker?.('origin');
                const d = window.getAirportPicker?.('destination');
                const originCode = o?.getCode?.() || form.querySelector('input[name="origin"]')?.value?.trim();
                const destCode = d?.getCode?.() || form.querySelector('input[name="destination"]')?.value?.trim();
                if (!originCode || !destCode) {
                    e.preventDefault();
                    alert('Please pick both places from the suggestions list.');
                    return;
                }
            }
        }

        e.preventDefault();
        try {
            setBusy(true, form);
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                redirect: 'follow',
            });
            const html = await res.text();
            // Use final URL after search redirect so pagination / filters stay in sync.
            await swapFlightsPageFromHtml(html, res.url || window.location.href);
        } catch (_err) {
            form.submit();
        } finally {
            setBusy(false);
        }
    });
})();
</script>
