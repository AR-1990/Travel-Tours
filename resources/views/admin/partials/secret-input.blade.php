@props([
    'name',
    'id' => null,
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'autocomplete' => 'off',
    'inputClass' => 'form-control',
    'visibleByDefault' => true,
])

@php
    $fieldId = $id ?? 'secret_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $isVisible = filter_var($visibleByDefault, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="input-group secret-input-group">
    <input
        type="{{ $isVisible ? 'text' : 'password' }}"
        name="{{ $name }}"
        id="{{ $fieldId }}"
        class="{{ $inputClass }} secret-field"
        autocomplete="{{ $autocomplete }}"
        value="{{ $value }}"
        @if($required) required @endif
        @if($placeholder !== '') placeholder="{{ $placeholder }}" @endif
    >
    <button
        type="button"
        class="btn btn-outline-secondary secret-toggle-btn"
        data-target="{{ $fieldId }}"
        title="{{ $isVisible ? 'Hide value' : 'Show value' }}"
        aria-label="{{ $isVisible ? 'Hide value' : 'Show value' }}"
        aria-pressed="{{ $isVisible ? 'true' : 'false' }}"
    >
        <i class="fas {{ $isVisible ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
    </button>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function setSecretVisible(btn, visible) {
                    const input = document.getElementById(btn.dataset.target);
                    if (!input) {
                        return;
                    }
                    input.type = visible ? 'text' : 'password';
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye', !visible);
                        icon.classList.toggle('fa-eye-slash', visible);
                    }
                    btn.setAttribute('aria-pressed', visible ? 'true' : 'false');
                    btn.title = visible ? 'Hide value' : 'Show value';
                    btn.setAttribute('aria-label', visible ? 'Hide value' : 'Show value');
                }

                document.querySelectorAll('.secret-toggle-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const input = document.getElementById(btn.dataset.target);
                        if (!input) {
                            return;
                        }
                        setSecretVisible(btn, input.type === 'password');
                    });
                });

                document.querySelectorAll('[data-secret-toggle-all]').forEach(function (master) {
                    master.addEventListener('click', function () {
                        const show = master.dataset.secretToggleAll === 'show';
                        document.querySelectorAll('.secret-input-group .secret-toggle-btn').forEach(function (btn) {
                            setSecretVisible(btn, show);
                        });
                    });
                });
            })();
        </script>
    @endpush
@endonce
