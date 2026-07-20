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
                const url = `${this.searchUrl}?q=${encodeURIComponent(q)}&limit=15`;
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
                li.innerHTML = `
                    <span class="airport-picker-item-code">${item.code}</span>
                    <span class="airport-picker-item-main">${item.city || item.name}</span>
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
