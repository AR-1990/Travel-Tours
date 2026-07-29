/**
 * Homepage / public flight search — multi-city legs + trip type sync.
 */
(function () {
    function setSectionDisabled(root, disabled) {
        if (!root) return;
        root.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el.id === 'homeAddMultiCityLeg' || el.classList.contains('remove-home-multi-city-leg') || el.classList.contains('home-multicity-swap')) {
                el.disabled = false;
                return;
            }
            if (el.classList.contains('plus-btn') || el.classList.contains('minus-btn')) {
                el.disabled = disabled;
                return;
            }
            el.disabled = disabled;
        });
    }

    function renumberHomeLegs(container) {
        container.querySelectorAll('.home-multicity-leg').forEach((row, index) => {
            row.dataset.legIndex = String(index);
            const num = row.querySelector('.leg-number');
            if (num) num.textContent = String(index + 1);
            const removeBtn = row.querySelector('.remove-home-multi-city-leg');
            if (removeBtn) {
                removeBtn.setAttribute('aria-label', 'Remove flight ' + (index + 1));
            }

            row.querySelectorAll('[id]').forEach((el) => {
                el.id = el.id.replace(/home_leg_(?:__INDEX__|\d+)/g, 'home_leg_' + index);
            });
            row.querySelectorAll('[aria-controls]').forEach((el) => {
                el.setAttribute(
                    'aria-controls',
                    el.getAttribute('aria-controls').replace(/home_leg_(?:__INDEX__|\d+)/g, 'home_leg_' + index)
                );
            });
            row.querySelectorAll('[for]').forEach((el) => {
                el.setAttribute(
                    'for',
                    el.getAttribute('for').replace(/home_leg_(?:__INDEX__|\d+)/g, 'home_leg_' + index)
                );
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
                dateInput.id = 'home_leg_' + index + '_date';
                if (window.jQuery) {
                    const $input = window.jQuery(dateInput);
                    if ($input.hasClass('hasDatepicker')) {
                        $input.datepicker('destroy');
                    }
                    $input.datepicker();
                }
            }
        });
        window.initAirportPickers?.();
    }

    function syncHomeTripType(form) {
        const trip = form.querySelector('input[name="trip_type"]:checked')?.value || 'one-way';
        const isMulti = trip === 'multi-city';
        const isRound = trip === 'round-way';
        const simple = document.getElementById('homeSimpleRoute');
        const multi = document.getElementById('homeMultiCity');

        if (simple) simple.style.display = isMulti ? 'none' : '';
        if (multi) multi.style.display = isMulti ? '' : 'none';
        setSectionDisabled(simple, isMulti);
        setSectionDisabled(multi, !isMulti);

        const returnWrap = form.querySelector('.search-form-return');
        if (returnWrap) {
            returnWrap.style.display = isRound && !isMulti ? '' : 'none';
        }
        const returnInput = form.querySelector('input[name="return_date"]');
        if (returnInput && !isRound) {
            returnInput.value = '';
        }
    }

    window.initHomeFlightSearch = function initHomeFlightSearch() {
        if (window.__homeFlightSearchReady) {
            return;
        }
        window.__homeFlightSearchReady = true;

        window.initAirportPickers?.();

        document.getElementById('homeSwapAirports')?.addEventListener('click', function () {
            const o = window.getAirportPicker('origin');
            const d = window.getAirportPicker('destination');
            if (!o || !d) return;
            const oc = o.getCode(), od = o.display.value;
            const dc = d.getCode(), dd = d.display.value;
            o.setSelection(dc, dd);
            d.setSelection(oc, od);
        });

        const form = document.getElementById('homeFlightSearchForm');
        if (!form) return;

        const legsContainer = document.getElementById('homeMultiCityLegs');
        const template = document.getElementById('homeMultiCityLegTemplate');

        form.querySelectorAll('input[name="trip_type"]').forEach((radio) => {
            radio.addEventListener('change', () => syncHomeTripType(form));
        });
        syncHomeTripType(form);

        document.getElementById('homeAddMultiCityLeg')?.addEventListener('click', function () {
            if (!legsContainer || !template) return;
            if (legsContainer.querySelectorAll('.home-multicity-leg').length >= 6) {
                alert('Maximum of 6 flights for multi-destination search.');
                return;
            }
            const html = template.innerHTML.replace(/__INDEX__/g, String(legsContainer.children.length));
            const wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            const row = wrap.firstElementChild;
            if (!row) return;
            legsContainer.appendChild(row);
            renumberHomeLegs(legsContainer);
        });

        legsContainer?.addEventListener('click', function (e) {
            const swapBtn = e.target.closest('.home-multicity-swap');
            if (swapBtn) {
                const row = swapBtn.closest('.home-multicity-leg');
                if (!row) return;
                const pickers = row.querySelectorAll('.airport-picker');
                if (pickers.length < 2) return;
                const o = pickers[0]._airportPicker;
                const d = pickers[1]._airportPicker;
                if (!o || !d) return;
                const oc = o.getCode(), od = o.display.value;
                const dc = d.getCode(), dd = d.display.value;
                o.setSelection(dc, dd);
                d.setSelection(oc, od);
                return;
            }

            const removeBtn = e.target.closest('.remove-home-multi-city-leg');
            if (!removeBtn) return;
            const row = removeBtn.closest('.home-multicity-leg');
            if (!row) return;
            if (legsContainer.querySelectorAll('.home-multicity-leg').length <= 2) {
                alert('Multi-destination search needs at least two flights.');
                return;
            }
            row.remove();
            renumberHomeLegs(legsContainer);
        });

        form.addEventListener('submit', function (e) {
            const trip = form.querySelector('input[name="trip_type"]:checked')?.value || 'one-way';
            if (trip === 'multi-city') {
                const rows = legsContainer?.querySelectorAll('.home-multicity-leg') || [];
                let ok = rows.length >= 2;
                rows.forEach((row) => {
                    const o = row.querySelector('input[name*="[origin]"]')?.value?.trim();
                    const d = row.querySelector('input[name*="[destination]"]')?.value?.trim();
                    const date = row.querySelector('input[name*="[departure_date]"]')?.value?.trim();
                    if (!o || !d || !date || o === d) ok = false;
                });
                if (!ok) {
                    e.preventDefault();
                    alert('Please complete at least two multi-destination legs with different airports and dates.');
                }
                return;
            }

            const origin = form.querySelector('#homeSimpleRoute input[name="origin"]')?.value?.trim();
            const dest = form.querySelector('#homeSimpleRoute input[name="destination"]')?.value?.trim();
            if (!origin || !dest) {
                e.preventDefault();
                alert('Please select From and To airports from the list.');
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.initHomeFlightSearch?.());
    } else {
        window.initHomeFlightSearch?.();
    }
})();
