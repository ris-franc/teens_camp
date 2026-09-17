@props(['inputId' => 'pin', 'name' => 'pin', 'label' => 'Enter 4-Digit PIN'])

<div class="pinpad-component" id="pinpad-container-{{ $inputId }}">
    @if($label)
        <label class="form-label fw-bold text-center d-block mb-1">{{ $label }}</label>
    @endif

    <input type="hidden" id="{{ $inputId }}" name="{{ $name }}" value="" required>

    <!-- 4-Digit Display Boxes -->
    <div class="pin-display-container" id="pin-boxes-{{ $inputId }}">
        <div class="pin-digit-box active" data-index="0"></div>
        <div class="pin-digit-box" data-index="1"></div>
        <div class="pin-digit-box" data-index="2"></div>
        <div class="pin-digit-box" data-index="3"></div>
    </div>

    <!-- Virtual Keypad -->
    <div class="pin-keypad">
        <button type="button" class="pin-key" data-digit="1">1</button>
        <button type="button" class="pin-key" data-digit="2">2</button>
        <button type="button" class="pin-key" data-digit="3">3</button>
        
        <button type="button" class="pin-key" data-digit="4">4</button>
        <button type="button" class="pin-key" data-digit="5">5</button>
        <button type="button" class="pin-key" data-digit="6">6</button>
        
        <button type="button" class="pin-key" data-digit="7">7</button>
        <button type="button" class="pin-key" data-digit="8">8</button>
        <button type="button" class="pin-key" data-digit="9">9</button>
        
        <button type="button" class="pin-key text-muted" data-action="clear" title="Clear PIN">
            <i class="bi bi-x-lg"></i>
        </button>
        <button type="button" class="pin-key" data-digit="0">0</button>
        <button type="button" class="pin-key text-muted" data-action="backspace" title="Backspace">
            <i class="bi bi-backspace-fill"></i>
        </button>
    </div>

    <div class="text-center text-muted small mt-2">
        <i class="bi bi-keyboard me-1"></i> You can also use your physical numeric keypad
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.initPinPad("{{ $inputId }}", "pinpad-container-{{ $inputId }}");
    });
</script>
@endpush
