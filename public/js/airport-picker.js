/**
 * Airport / city picker — searchable dropdown for IATA codes.
 */
(function () {
    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    class AirportPicker {
        constructor(root) {
            this.root = root;
            this.display = root.querySelector('.airport-picker-display');
            this.hidden = root.querySelector('.airport-picker-code');
            this.list = root.querySelector('.airport-picker-list');
            this.searchUrl = root.dataset.searchUrl || '/api/airports/search';
            this.activeIndex = -1;
            this.results = [];

            const code = root.dataset.initialCode || '';
            const label = root.dataset.initialLabel || '';
            if (code) {
                this.hidden.value = code;
            }
            if (label) {
                this.display.value = label;
            } else if (code) {
                this.fetchOne(code);
            }

            this.display.addEventListener('focus', () => this.search(''));
            this.display.addEventListener('input', debounce(() => {
                if (this.display.value.trim() === '') {
                    this.hidden.value = '';
                }
                this.search(this.display.value);
            }, 220));
            this.display.addEventListener('keydown', (e) => this.onKeydown(e));
            this.display.addEventListener('blur', () => setTimeout(() => this.close(), 180));
            document.addEventListener('click', (e) => {
                if (!this.root.contains(e.target)) {
                    this.close();
                }
            });
        }

        getProvider() {
            const form = this.root.closest('form');
            const checked = form?.querySelector('input[name="provider"]:checked:not(:disabled)');
            if (checked?.value) {
                return checked.value;
            }
            return this.root.dataset.provider || '';
        }

        async fetchOne(code) {
            try {
                const res = await fetch(`/api/airports/${encodeURIComponent(code)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.airport) {
                    this.select(data.airport, false);
                }
            } catch (_) {}
        }

        async search(q) {
            try {
                const provider = this.getProvider();
                const params = new URLSearchParams({
                    q: q || '',
                    limit: '15',
                });
                if (provider) {
                    params.set('provider', provider);
                }
                const url = `${this.searchUrl}?${params.toString()}`;
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.results = data.results || [];
                this.activeIndex = -1;
                this.render();
                this.open();
            } catch (_) {}
        }

        render() {
            this.list.innerHTML = '';
            if (!this.results.length) {
                const li = document.createElement('li');
                li.className = 'airport-picker-empty';
                li.textContent = 'No matches — try city, airport name, or IATA code';
                this.list.appendChild(li);
                return;
            }

            this.results.forEach((item, i) => {
                const li = document.createElement('li');
                li.className = 'airport-picker-item';
                li.setAttribute('role', 'option');
                li.dataset.index = String(i);
                const sub = [item.name, item.country].filter(Boolean).join(' · ');
                const main = item.label || [item.city, item.name].filter(Boolean).join(' — ') || item.code;
                li.innerHTML = `
                    <span class="airport-picker-item-code">${item.code}</span>
                    <span class="airport-picker-item-main">${main}</span>
                    <span class="airport-picker-item-sub">${sub}</span>
                `;
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.select(item);
                });
                this.list.appendChild(li);
            });
        }

        select(item, focusDisplay = true) {
            this.hidden.value = item.code;
            this.display.value = item.label || `${item.city} — ${item.name} (${item.code})`;
            this.close();
            this.root.dispatchEvent(new CustomEvent('airport-selected', { detail: item, bubbles: true }));
            if (focusDisplay) {
                this.display.blur();
            }
        }

        onKeydown(e) {
            if (!this.list || this.list.hidden) {
                if (e.key === 'ArrowDown') {
                    this.search(this.display.value);
                }
                return;
            }

            const items = this.list.querySelectorAll('.airport-picker-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.activeIndex = Math.min(this.activeIndex + 1, items.length - 1);
                this.highlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.activeIndex = Math.max(this.activeIndex - 1, 0);
                this.highlight(items);
            } else if (e.key === 'Enter' && this.activeIndex >= 0) {
                e.preventDefault();
                const item = this.results[this.activeIndex];
                if (item) this.select(item);
            } else if (e.key === 'Escape') {
                this.close();
            }
        }

        highlight(items) {
            items.forEach((el, i) => el.classList.toggle('active', i === this.activeIndex));
            items[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
        }

        open() {
            this.list.hidden = false;
            this.display.setAttribute('aria-expanded', 'true');
        }

        close() {
            this.list.hidden = true;
            this.display.setAttribute('aria-expanded', 'false');
            this.activeIndex = -1;
        }

        getCode() {
            return this.hidden.value;
        }

        setSelection(code, label) {
            this.hidden.value = code;
            this.display.value = label || code;
        }
    }

    function initAll() {
        document.querySelectorAll('.airport-picker').forEach((el) => {
            if (el._airportPicker) return;
            el._airportPicker = new AirportPicker(el);
        });
    }

    window.AirportPicker = AirportPicker;
    window.initAirportPickers = initAll;

    window.getAirportPicker = function (nameOrId) {
        const el = document.querySelector(`.airport-picker[data-field="${nameOrId}"]`)
            || document.querySelector(`#${nameOrId}`)?.closest('.airport-picker');
        return el?._airportPicker || null;
    };

    /**
     * Apply provider-specific From/To defaults and constrain the airport picker scope.
     * @param {boolean} forceDefaults When true (provider radio change), always set From/To.
     */
    window.syncAirportPickersForProvider = function (form, options, forceDefaults) {
        if (!form) return;
        const opts = options || {};
        const provider = form.querySelector('input[name="provider"]:checked:not(:disabled)')?.value || '';
        const isSun = provider === 'sunspring';
        const allowed = new Set((opts.allowedCodes || []).map((c) => String(c).toUpperCase()));

        const sunOrigin = opts.defaultOrigin || { code: 'THR', label: 'Tehran — Mehrabad (THR)' };
        const sunDest = opts.defaultDestination || { code: 'MHD', label: 'Mashhad (MHD)' };
        const tpOrigin = opts.travelportOrigin || {
            code: form.dataset.tpOriginCode || 'LHR',
            label: form.dataset.tpOriginLabel || 'London — Heathrow (LHR)',
        };
        const tpDest = opts.travelportDestination || {
            code: form.dataset.tpDestCode || 'JFK',
            label: form.dataset.tpDestLabel || 'New York — JFK (JFK)',
        };

        const targetOrigin = isSun ? sunOrigin : tpOrigin;
        const targetDest = isSun ? sunDest : tpDest;

        form.querySelectorAll('.airport-picker').forEach((el) => {
            el.dataset.provider = provider;
            const picker = el._airportPicker;
            if (!picker) return;

            const code = (picker.getCode() || '').toUpperCase();
            const field = el.dataset.field || '';
            const isDestination = field.includes('destination') || field.endsWith('destination');
            const isOrigin = field.includes('origin') || field === 'origin' || field.endsWith('[origin]');

            // Multi-city middle legs: only fix invalid SunSpring codes unless forcing simple route.
            const isSimpleRoute = field === 'origin' || field === 'destination';

            if (forceDefaults && isSimpleRoute) {
                if (isDestination) {
                    picker.setSelection(targetDest.code, targetDest.label);
                } else if (isOrigin) {
                    picker.setSelection(targetOrigin.code, targetOrigin.label);
                }
                return;
            }

            if (isSun && allowed.size && code && !allowed.has(code)) {
                if (isDestination) {
                    picker.setSelection(sunDest.code, sunDest.label);
                } else {
                    picker.setSelection(sunOrigin.code, sunOrigin.label);
                }
            }
        });

        if (forceDefaults) {
            applyMultiCityDefaults(form, isSun, opts);
        }

        const help = form.querySelector('[data-provider-airport-help]');
        if (help) {
            help.hidden = !isSun;
        }

        const worldPopular = form.querySelector('[data-popular-routes="travelport"]');
        const sunPopular = form.querySelector('[data-popular-routes="sunspring"]');
        if (worldPopular) worldPopular.hidden = isSun;
        if (sunPopular) sunPopular.hidden = !isSun;
    };

    function applyMultiCityDefaults(form, isSun, opts) {
        const legs = form.querySelectorAll('.multi-city-leg, .home-multicity-leg');
        if (!legs.length) return;

        const sun = {
            o1: opts.defaultOrigin || { code: 'THR', label: 'THR' },
            d1: { code: 'SYZ', label: form.dataset.ssMidLabel || 'Shiraz (SYZ)' },
            o2: { code: 'SYZ', label: form.dataset.ssMidLabel || 'Shiraz (SYZ)' },
            d2: opts.defaultDestination || { code: 'MHD', label: 'MHD' },
        };
        const tp = {
            o1: opts.travelportOrigin || { code: form.dataset.tpOriginCode || 'LHR', label: form.dataset.tpOriginLabel || 'LHR' },
            d1: { code: 'CDG', label: form.dataset.tpMidLabel || 'Paris — Charles de Gaulle (CDG)' },
            o2: { code: 'CDG', label: form.dataset.tpMidLabel || 'Paris — Charles de Gaulle (CDG)' },
            d2: opts.travelportDestination || { code: form.dataset.tpDestCode || 'JFK', label: form.dataset.tpDestLabel || 'JFK' },
        };
        const map = isSun ? sun : tp;

        legs.forEach((row, index) => {
            const originPicker = row.querySelector('.airport-picker[data-field*="[origin]"]')?._airportPicker;
            const destPicker = row.querySelector('.airport-picker[data-field*="[destination]"]')?._airportPicker;
            if (index === 0) {
                originPicker?.setSelection(map.o1.code, map.o1.label);
                destPicker?.setSelection(map.d1.code, map.d1.label);
            } else if (index === 1) {
                originPicker?.setSelection(map.o2.code, map.o2.label);
                destPicker?.setSelection(map.d2.code, map.d2.label);
            }
        });
    }

    window.bindFlightProviderAirportScope = function (form, options) {
        if (!form || form.dataset.providerAirportBound === '1') return;
        form.dataset.providerAirportBound = '1';
        // Initial page load: only constrain invalid SunSpring picks, keep existing values.
        window.syncAirportPickersForProvider(form, options, false);
        form.querySelectorAll('input[name="provider"]').forEach((input) => {
            input.addEventListener('change', () => {
                window.syncAirportPickersForProvider(form, options, true);
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
