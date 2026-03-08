@props([
    'name' => 'pin',
    'length' => 6,
    'prefix' => 'IG-',
])

<div class="pin-input" x-data="pinInput({{ $length }})" x-init="init()">
    <span class="pin-input-prefix">{{ $prefix }}</span>
    <div class="pin-input-boxes">
        @for ($i = 0; $i < $length; $i++)
            <input
                type="text"
                inputmode="numeric"
                maxlength="1"
                x-ref="pin{{ $i }}"
                x-model="digits[{{ $i }}]"
                x-on:input="handleInput($event, {{ $i }})"
                x-on:keydown="handleKeydown($event, {{ $i }})"
                x-on:paste.prevent="handlePaste($event)"
                x-on:focus="$event.target.select()"
                class="pin-input-box"
                autocomplete="off"
                aria-label="@lang('ig-user::pin_login.pin_digit', ['digit' => $i + 1])"
            />
        @endfor
    </div>
    <input type="hidden" name="{{ $name }}" x-bind:value="fullPin" />
</div>

<script>
    function pinInput(length) {
        return {
            digits: Array(length).fill(''),
            length: length,
            get fullPin() {
                return this.digits.join('');
            },
            init() {
                setTimeout(() => this.$refs.pin0?.focus(), 50);
            },
            handleInput(event, index) {
                const value = event.target.value;
                // Only allow digits
                if (!/^\d$/.test(value)) {
                    this.digits[index] = '';
                    return;
                }
                this.digits[index] = value;
                // Move to next input
                if (index < this.length - 1) {
                    this.$refs['pin' + (index + 1)]?.focus();
                }
            },
            handleKeydown(event, index) {
                if (event.key === 'Backspace') {
                    if (this.digits[index] === '' && index > 0) {
                        // Move to previous input and clear it
                        this.$refs['pin' + (index - 1)]?.focus();
                        this.digits[index - 1] = '';
                        event.preventDefault();
                    } else {
                        this.digits[index] = '';
                    }
                } else if (event.key === 'ArrowLeft' && index > 0) {
                    this.$refs['pin' + (index - 1)]?.focus();
                    event.preventDefault();
                } else if (event.key === 'ArrowRight' && index < this.length - 1) {
                    this.$refs['pin' + (index + 1)]?.focus();
                    event.preventDefault();
                } else if (event.key === 'Delete') {
                    this.digits[index] = '';
                }
            },
            handlePaste(event) {
                const pasted = (event.clipboardData || window.clipboardData).getData('text');
                // Strip non-digits (handles "IG-1 2 3 4 5 6", "IG-123456", "123456", etc.)
                const digits = pasted.replace(/\D/g, '').slice(0, this.length);
                for (let i = 0; i < this.length; i++) {
                    this.digits[i] = digits[i] || '';
                }
                // Focus last filled or last box
                const focusIndex = Math.min(digits.length, this.length - 1);
                this.$refs['pin' + focusIndex]?.focus();
            },
        };
    }
</script>
