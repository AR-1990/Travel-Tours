@php
    $idPrefix = $idPrefix ?? '';
    $adults = (int) ($homeFlightInput['adults'] ?? 1);
@endphp
<div class="form-group dropdown passenger-box">
    <div class="passenger-class" role="menu" data-bs-toggle="dropdown" aria-expanded="false">
        <label>Passenger, Class</label>
        <div class="form-group-icon">
            <div class="passenger-total">
                <span class="passenger-total-amount">{{ $adults }}</span> Passenger
            </div>
            <i class="fal fa-user-tie-hair"></i>
        </div>
        <p class="passenger-class-name">Economy</p>
    </div>
    <div class="dropdown-menu dropdown-menu-end">
        <div class="dropdown-item">
            <div class="passenger-item">
                <div class="passenger-info">
                    <h6>Adults</h6>
                    <p>12+ Years</p>
                </div>
                <div class="passenger-qty">
                    <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                    <input type="text" name="adults" class="qty-amount passenger-adult"
                        value="{{ $adults }}" readonly>
                    <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="dropdown-item">
            <div class="passenger-item">
                <div class="passenger-info">
                    <h6>Children</h6>
                    <p>2-12 Years</p>
                </div>
                <div class="passenger-qty">
                    <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                    <input type="text" name="children" class="qty-amount passenger-children" value="0" readonly>
                    <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="dropdown-item">
            <div class="passenger-item">
                <div class="passenger-info">
                    <h6>Infant</h6>
                    <p>Below 2 Years</p>
                </div>
                <div class="passenger-qty">
                    <button type="button" class="minus-btn"><i class="far fa-minus"></i></button>
                    <input type="text" name="infant" class="qty-amount passenger-infant" value="0" readonly>
                    <button type="button" class="plus-btn"><i class="far fa-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="dropdown-item">
            <h6 class="mb-3 mt-2">Cabin Class</h6>
            <div class="passenger-class-info">
                <div class="form-check">
                    <input class="form-check-input" type="radio" value="Economy" name="cabin_class" id="{{ $idPrefix }}cabin-class1" checked>
                    <label class="form-check-label" for="{{ $idPrefix }}cabin-class1">Economy</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" value="Business" name="cabin_class" id="{{ $idPrefix }}cabin-class2">
                    <label class="form-check-label" for="{{ $idPrefix }}cabin-class2">Business</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" value="First Class" name="cabin_class" id="{{ $idPrefix }}cabin-class3">
                    <label class="form-check-label" for="{{ $idPrefix }}cabin-class3">First Class</label>
                </div>
            </div>
        </div>
    </div>
</div>
