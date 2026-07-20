@php
    $fieldName = $name ?? 'origin';
    $pickerId = $id ?? $fieldName;
    $code = strtoupper((string) old($fieldName, $value ?? ''));
    $airport = $code !== '' ? \App\Support\AirportDirectory::find($code) : null;
    $initialLabel = $airport['label'] ?? '';
    $placeholder = $placeholder ?? 'City or airport';
    $searchUrl = $searchUrl ?? route('api.airports.search');
    $icon = $icon ?? ($fieldName === 'destination' ? 'fa-plane-arrival' : 'fa-plane-departure');
@endphp
<div class="airport-picker"
    data-field="{{ $fieldName }}"
    data-initial-code="{{ $code }}"
    data-initial-label="{{ $initialLabel }}"
    data-search-url="{{ $searchUrl }}">
    <label class="flight-field-label" for="{{ $pickerId }}_display">{{ $label ?? ($fieldName === 'destination' ? 'To' : 'From') }}</label>
    <div class="airport-picker-input-wrap">
        <i class="fas {{ $icon }} airport-picker-icon" aria-hidden="true"></i>
        <input type="text"
            id="{{ $pickerId }}_display"
            class="form-control airport-picker-display"
            placeholder="{{ $placeholder }}"
            value="{{ $initialLabel }}"
            autocomplete="off"
            autocorrect="off"
            spellcheck="false"
            role="combobox"
            aria-expanded="false"
            aria-autocomplete="list"
            aria-controls="{{ $pickerId }}_list">
        <input type="hidden" name="{{ $fieldName }}" id="{{ $pickerId }}" class="airport-picker-code" value="{{ $code }}" required>
    </div>
    <ul id="{{ $pickerId }}_list" class="airport-picker-list" role="listbox" hidden></ul>
</div>
