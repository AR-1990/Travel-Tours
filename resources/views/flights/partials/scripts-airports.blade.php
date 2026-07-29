<script src="{{ asset('js/airport-picker.js') }}"></script>
<script>
(function () {
    function swapPickers() {
        const o = window.getAirportPicker('origin');
        const d = window.getAirportPicker('destination');
        if (!o || !d) return;
        const oc = o.getCode(), od = o.display.value;
        const dc = d.getCode(), dd = d.display.value;
        o.setSelection(dc, dd);
        d.setSelection(oc, od);
    }

    function setDisabled(root, disabled) {
        if (!root) return;
        root.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el.type === 'hidden' && el.name === '_token') return;
            if (el.id === 'addMultiCityLeg') {
                el.disabled = disabled;
                return;
            }
            if (el.classList.contains('remove-multi-city-leg')) {
                el.disabled = disabled;
                return;
            }
            el.disabled = disabled;
        });
    }

    function renumberLegs(container) {
        container.querySelectorAll('.multi-city-leg').forEach((row, index) => {
            row.dataset.legIndex = String(index);
            const num = row.querySelector('.leg-number');
            if (num) num.textContent = String(index + 1);

            row.querySelectorAll('[id]').forEach((el) => {
                el.id = el.id.replace(/leg_(?:__INDEX__|\d+)/g, 'leg_' + index);
            });
            row.querySelectorAll('[aria-controls]').forEach((el) => {
                el.setAttribute('aria-controls', el.getAttribute('aria-controls').replace(/leg_(?:__INDEX__|\d+)/g, 'leg_' + index));
            });
            row.querySelectorAll('[for]').forEach((el) => {
                el.setAttribute('for', el.getAttribute('for').replace(/leg_(?:__INDEX__|\d+)/g, 'leg_' + index));
            });
            row.querySelectorAll('.airport-picker').forEach((picker) => {
                const field = picker.dataset.field || '';
                if (field.includes('[origin]')) {
                    picker.dataset.field = 'legs[' + index + '][origin]';
                } else if (field.includes('[destination]')) {
                    picker.dataset.field = 'legs[' + index + '][destination]';
                }
                picker._airportPicker = null;
            });
            row.querySelectorAll('.airport-picker-code').forEach((input) => {
                if ((input.name || '').includes('[origin]')) {
                    input.name = 'legs[' + index + '][origin]';
                } else if ((input.name || '').includes('[destination]')) {
                    input.name = 'legs[' + index + '][destination]';
                }
            });
            const dateInput = row.querySelector('.multi-city-date');
            if (dateInput) {
                dateInput.name = 'legs[' + index + '][departure_date]';
                dateInput.id = 'leg_' + index + '_date';
            }
        });
        window.initAirportPickers?.();
    }

    window.initFlightUi = function initFlightUi() {
        document.getElementById('swapAirports')?.addEventListener('click', swapPickers);
        document.getElementById('swapAirportsMobile')?.addEventListener('click', swapPickers);

        document.querySelectorAll('.popular-routes button[data-origin]').forEach(btn => {
            if (btn.dataset.ajaxBound === '1') {
                return;
            }
            btn.dataset.ajaxBound = '1';
            btn.addEventListener('click', function () {
                const o = window.getAirportPicker('origin');
                const d = window.getAirportPicker('destination');
                if (o) o.setSelection(this.dataset.origin, this.dataset.oLabel || this.dataset.origin);
                if (d) d.setSelection(this.dataset.destination, this.dataset.dLabel || this.dataset.destination);
            });
        });

        const form = document.getElementById('flightSearchForm');
        if (!form) return;

        const tripRadios = form.querySelectorAll('input[name="trip_type"]');
        const returnWrap = document.getElementById('returnDateWrap');
        const returnInput = document.getElementById('return_date');
        const simple = document.getElementById('simpleRouteFields');
        const multi = document.getElementById('multiCityFields');
        const popular = document.getElementById('popularRoutesWrap');
        const legsContainer = document.getElementById('multiCityLegs');
        const addBtn = document.getElementById('addMultiCityLeg');
        const template = document.getElementById('multiCityLegTemplate');

        function syncTripType() {
            const trip = form.querySelector('input[name="trip_type"]:checked')?.value || 'oneway';
            const isMulti = trip === 'multicity';
            const isRound = trip === 'roundtrip';

            if (simple) simple.style.display = isMulti ? 'none' : '';
            if (multi) multi.style.display = isMulti ? '' : 'none';
            if (popular) popular.style.display = isMulti ? 'none' : '';
            setDisabled(simple, isMulti);
            setDisabled(multi, !isMulti);

            if (returnWrap) returnWrap.style.display = isRound ? '' : 'none';
            if (returnInput) {
                if (!isRound) returnInput.value = '';
                returnInput.required = isRound && !isMulti;
            }
            const dep = document.getElementById('departure_date');
            if (dep) dep.required = !isMulti;
            const origin = document.getElementById('origin');
            const dest = document.getElementById('destination');
            if (origin) origin.required = !isMulti;
            if (dest) dest.required = !isMulti;
        }

        tripRadios.forEach(r => r.addEventListener('change', syncTripType));
        syncTripType();

        addBtn?.addEventListener('click', function () {
            if (!legsContainer || !template) return;
            if (legsContainer.querySelectorAll('.multi-city-leg').length >= 6) {
                alert('Maximum of 6 flights for multi-city search.');
                return;
            }
            const html = template.innerHTML.replace(/__INDEX__/g, String(legsContainer.children.length));
            const wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            const row = wrap.firstElementChild;
            if (!row) return;
            legsContainer.appendChild(row);
            renumberLegs(legsContainer);
        });

        legsContainer?.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-multi-city-leg');
            if (!btn) return;
            const row = btn.closest('.multi-city-leg');
            if (!row) return;
            if (legsContainer.querySelectorAll('.multi-city-leg').length <= 2) {
                alert('Multi-city search needs at least two flights.');
                return;
            }
            row.remove();
            renumberLegs(legsContainer);
        });

        form.addEventListener('submit', function (e) {
            const trip = form.querySelector('input[name="trip_type"]:checked')?.value || 'oneway';
            if (trip === 'multicity') {
                const rows = legsContainer?.querySelectorAll('.multi-city-leg') || [];
                let ok = rows.length >= 2;
                rows.forEach((row) => {
                    const o = row.querySelector('input[name*="[origin]"]')?.value?.trim();
                    const d = row.querySelector('input[name*="[destination]"]')?.value?.trim();
                    const date = row.querySelector('input[name*="[departure_date]"]')?.value?.trim();
                    if (!o || !d || !date || o === d) ok = false;
                });
                if (!ok) {
                    e.preventDefault();
                    alert('Please complete at least two multi-city legs with different airports and dates.');
                }
                return;
            }

            const origin = form.querySelector('input[name="origin"]')?.value?.trim();
            const dest = form.querySelector('input[name="destination"]')?.value?.trim();
            if (!origin || !dest) {
                e.preventDefault();
                alert('Please select From and To airports from the list.');
            }
        });
    };

    if (typeof window.initFlightUi === 'function') {
        window.initFlightUi();
    }
})();
</script>
