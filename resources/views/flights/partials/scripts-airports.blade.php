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
    document.getElementById('swapAirports')?.addEventListener('click', swapPickers);
    document.getElementById('swapAirportsMobile')?.addEventListener('click', swapPickers);

    document.querySelectorAll('.popular-routes button[data-origin]').forEach(btn => {
        btn.addEventListener('click', function () {
            const o = window.getAirportPicker('origin');
            const d = window.getAirportPicker('destination');
            if (o) o.setSelection(this.dataset.origin, this.dataset.oLabel || this.dataset.origin);
            if (d) d.setSelection(this.dataset.destination, this.dataset.dLabel || this.dataset.destination);
        });
    });

    const form = document.getElementById('flightSearchForm');
    if (form) {
        const tripRadios = form.querySelectorAll('input[name="trip_type"]');
        const returnWrap = document.getElementById('returnDateWrap');
        const returnInput = document.getElementById('return_date');
        function syncTripType() {
            const round = form.querySelector('input[name="trip_type"]:checked')?.value === 'roundtrip';
            if (returnWrap) returnWrap.style.display = round ? '' : 'none';
            if (returnInput) {
                if (!round) returnInput.value = '';
                returnInput.required = round;
            }
        }
        tripRadios.forEach(r => r.addEventListener('change', syncTripType));
        syncTripType();
    }
})();
</script>
