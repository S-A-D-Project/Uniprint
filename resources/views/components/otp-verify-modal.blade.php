{{-- OTP Verification Modal Component --}}
{{-- Usage: <x-otp-verify-modal :user="$user" :redirect-url="$redirectUrl" :action="'login'" /> --}}

<div x-data="otpVerifyModal()" x-init="init()">
    {{-- Trigger Button (optional, can also be triggered programmatically) --}}
    @if($showTrigger ?? true)
    <button type="button"
            @click="open()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth"
            {{ $triggerAttributes ?? '' }}>
        <i data-lucide="shield-check" class="h-5 w-5"></i>
        <span class="text-sm font-medium">{{ $triggerText ?? 'Verify with OTP' }}</span>
    </button>
    @endif

    {{-- Modal Overlay --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
         style="display: none;"
         @click="close()">
    </div>

    {{-- Modal Content --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="close()">
        <div class="bg-popover border border-border rounded-xl shadow-card-hover w-full max-w-md overflow-hidden"
             @click.stop>
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="mail-open" class="h-8 w-8 text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold">Enter Verification Code</h3>
                <p class="text-sm text-muted-foreground mt-1">
                    We sent a 6-digit code to<br>
                    <span class="font-medium text-foreground">{{ $user->email ?? 'your email' }}</span>
                </p>
            </div>

            {{-- Body --}}
            <div class="p-6">
                {{-- Step 1: Request OTP --}}
                <div x-show="!otpSent && !loading" x-cloak class="text-center py-4">
                    <p class="text-sm text-muted-foreground mb-6">
                        Click the button below to receive your verification code.
                    </p>
                    <button type="button"
                            @click="requestOtp()"
                            class="px-6 py-3 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth">
                        Send Verification Code
                    </button>
                </div>

                {{-- Loading State --}}
                <div x-show="loading && !otpSent" x-cloak class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                    <p class="text-sm text-muted-foreground">Sending verification code...</p>
                </div>

                {{-- Step 2: Enter OTP --}}
                <div x-show="otpSent && !verified" x-cloak>
                    {{-- OTP Input Boxes --}}
                    <div class="flex justify-center gap-2 mb-6">
                        <template x-for="(digit, index) in 6" :key="index">
                            <input type="text"
                                   inputmode="numeric"
                                   maxlength="1"
                                   class="w-12 h-14 text-center text-2xl font-bold rounded-lg border-2 border-input bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                   :class="{ 'border-destructive': errorMessage, 'border-primary': focusedIndex === index }"
                                   x-model="otpDigits[index]"
                                   @input="handleOtpInput($event, index)"
                                   @keydown.backspace="handleOtpBackspace($event, index)"
                                   @paste="handleOtpPaste($event)"
                                   @focus="focusedIndex = index"
                                   @blur="focusedIndex = null"
                                   :ref="'otpInput' + index"
                                   autocomplete="one-time-code">
                        </template>
                    </div>

                    {{-- Timer Display --}}
                    <div class="text-center mb-4">
                        <p class="text-sm text-muted-foreground">
                            Code expires in <span class="font-semibold" :class="timeLeft < 60 ? 'text-destructive' : 'text-foreground'" x-text="formatTimeLeft()"></span>
                        </p>
                    </div>

                    {{-- Error Message --}}
                    <div x-show="errorMessage" x-cloak class="mb-4 p-3 bg-destructive/10 rounded-lg flex items-start gap-2">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-destructive flex-shrink-0 mt-0.5"></i>
                        <p class="text-sm text-destructive" x-text="errorMessage"></p>
                    </div>

                    {{-- Attempts Left --}}
                    <div x-show="attemptsLeft < 3" x-cloak class="mb-4 text-center">
                        <p class="text-sm text-muted-foreground">
                            <span x-text="attemptsLeft"></span> attempt<span x-show="attemptsLeft !== 1">s</span> remaining
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-3">
                        <button type="button"
                                @click="verifyOtp()"
                                class="w-full px-4 py-3 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth font-medium"
                                :disabled="!isOtpComplete() || verifying || attemptsLeft === 0"
                                :class="{ 'opacity-50 cursor-not-allowed': !isOtpComplete() || attemptsLeft === 0 }">
                            <span x-show="!verifying">Verify & Continue</span>
                            <span x-show="verifying" class="flex items-center justify-center gap-2">
                                <span class="animate-spin rounded-full h-4 w-4 border-2 border-primary-foreground border-t-transparent"></span>
                                Verifying...
                            </span>
                        </button>

                        <div class="flex justify-center items-center gap-4 text-sm">
                            <button type="button"
                                    @click="resendOtp()"
                                    class="text-primary hover:underline"
                                    :disabled="resendCooldown > 0 || attemptsLeft === 0"
                                    :class="{ 'opacity-50 cursor-not-allowed': resendCooldown > 0 || attemptsLeft === 0 }">
                                <span x-show="resendCooldown === 0">Resend Code</span>
                                <span x-show="resendCooldown > 0">Resend in <span x-text="resendCooldown"></span>s</span>
                            </button>

                            <span class="text-border">|</span>

                            <button type="button"
                                    @click="close()"
                                    class="text-muted-foreground hover:text-foreground">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Verified Success --}}
                <div x-show="verified" x-cloak class="text-center py-4">
                    <div class="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="h-8 w-8 text-success"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Verification Successful!</h4>
                    <p class="text-sm text-muted-foreground">Redirecting you...</p>
                </div>

                {{-- Max Attempts Exceeded --}}
                <div x-show="attemptsLeft === 0 && !verified && otpSent" x-cloak class="text-center py-4">
                    <div class="w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="shield-alert" class="h-8 w-8 text-destructive"></i>
                    </div>
                    <h4 class="font-semibold mb-2">Too Many Attempts</h4>
                    <p class="text-sm text-muted-foreground mb-4">Please request a new verification code.</p>
                    <button type="button"
                            @click="resetAndRequest()"
                            class="px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth">
                        Request New Code
                    </button>
                </div>
            </div>

            {{-- Footer Security Note --}}
            <div class="px-6 py-3 bg-accent/30 border-t border-border">
                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                    <i data-lucide="lock" class="h-4 w-4"></i>
                    <span>Secure verification. Code expires in 10 minutes.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function otpVerifyModal() {
    return {
        isOpen: {{ $autoOpen ?? false ? 'true' : 'false' }},
        loading: false,
        verifying: false,
        otpSent: false,
        verified: false,
        otpDigits: ['', '', '', '', '', ''],
        focusedIndex: null,
        errorMessage: '',
        attemptsLeft: 3,
        resendCooldown: 0,
        otpExpiresAt: null,
        resendTimer: null,
        countdownTimer: null,
        redirectUrl: '{{ $redirectUrl ?? route('home') }}',
        action: '{{ $action ?? 'verify' }}',

        init() {
            this.$watch('isOpen', value => {
                if (value) {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        if (this.otpSent) {
                            this.focusFirstInput();
                        }
                    });
                }
            });

            // Listen for programmatic open event
            window.addEventListener('open-otp-verify-modal', () => {
                this.open();
            });
        },

        open() {
            this.isOpen = true;
            if (!this.otpSent) {
                this.requestOtp();
            }
        },

        close() {
            this.isOpen = false;
            this.resetState();
        },

        resetState() {
            this.loading = false;
            this.verifying = false;
            this.otpDigits = ['', '', '', '', '', ''];
            this.focusedIndex = null;
            this.errorMessage = '';
            if (this.resendTimer) {
                clearInterval(this.resendTimer);
                this.resendTimer = null;
            }
            if (this.countdownTimer) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        },

        fullReset() {
            this.otpSent = false;
            this.verified = false;
            this.attemptsLeft = 3;
            this.resendCooldown = 0;
            this.otpExpiresAt = null;
            this.resetState();
        },

        isOtpComplete() {
            return this.otpDigits.every(d => d && d.length === 1 && /^\d$/.test(d));
        },

        getOtpString() {
            return this.otpDigits.join('');
        },

        focusFirstInput() {
            this.$nextTick(() => {
                const firstInput = this.$refs['otpInput0'];
                if (firstInput) firstInput.focus();
            });
        },

        handleOtpInput(event, index) {
            let value = event.target.value;

            // Only allow digits
            value = value.replace(/\D/g, '');
            if (value) {
                this.otpDigits[index] = value[0];
            }

            // Auto-advance to next input
            if (value && index < 5) {
                this.$nextTick(() => {
                    const nextInput = this.$refs['otpInput' + (index + 1)];
                    if (nextInput) nextInput.focus();
                });
            }

            // Auto-submit if all digits filled
            if (this.isOtpComplete() && index === 5) {
                this.verifyOtp();
            }
        },

        handleOtpBackspace(event, index) {
            if (!this.otpDigits[index] && index > 0) {
                this.$nextTick(() => {
                    const prevInput = this.$refs['otpInput' + (index - 1)];
                    if (prevInput) {
                        prevInput.focus();
                        this.otpDigits[index - 1] = '';
                    }
                });
            }
        },

        handleOtpPaste(event) {
            event.preventDefault();
            const pastedData = (event.clipboardData || window.clipboardData).getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);
            
            if (digits.length > 0) {
                // Fill the OTP digits array
                for (let i = 0; i < 6; i++) {
                    this.otpDigits[i] = digits[i] || '';
                }
                
                // Focus the appropriate input
                this.$nextTick(() => {
                    const focusIndex = Math.min(digits.length, 5);
                    const input = this.$refs['otpInput' + focusIndex];
                    if (input) input.focus();
                    
                    // Auto-submit if all 6 digits were pasted
                    if (digits.length === 6 && this.isOtpComplete()) {
                        this.verifyOtp();
                    }
                });
            }
        },

        formatTimeLeft() {
            if (!this.otpExpiresAt) return '10:00';
            const diff = Math.max(0, Math.floor((this.otpExpiresAt - Date.now()) / 1000));
            const mins = Math.floor(diff / 60);
            const secs = diff % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        startCountdown() {
            if (this.countdownTimer) clearInterval(this.countdownTimer);
            this.countdownTimer = setInterval(() => {
                const timeLeft = Math.floor((this.otpExpiresAt - Date.now()) / 1000);
                if (timeLeft <= 0) {
                    clearInterval(this.countdownTimer);
                    this.errorMessage = 'Code has expired. Please request a new one.';
                }
            }, 1000);
        },

        startResendCooldown() {
            this.resendCooldown = 60;
            if (this.resendTimer) clearInterval(this.resendTimer);
            this.resendTimer = setInterval(() => {
                this.resendCooldown--;
                if (this.resendCooldown <= 0) {
                    clearInterval(this.resendTimer);
                    this.resendTimer = null;
                }
            }, 1000);
        },

        async requestOtp() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route('otp.send') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        email: '{{ $user?->email }}',
                        action: this.action
                    })
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    this.otpSent = true;
                    this.otpExpiresAt = Date.now() + (10 * 60 * 1000); // 10 minutes
                    this.startResendCooldown();
                    this.startCountdown();
                    this.$nextTick(() => {
                        this.focusFirstInput();
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                } else {
                    this.errorMessage = data.message || 'Failed to send verification code';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async resendOtp() {
            if (this.resendCooldown > 0) return;
            this.attemptsLeft = 3;
            await this.requestOtp();
        },

        async resetAndRequest() {
            this.fullReset();
            await this.requestOtp();
        },

        async verifyOtp() {
            if (!this.isOtpComplete()) return;
            if (this.attemptsLeft === 0) {
                this.errorMessage = 'Too many failed attempts. Please request a new code.';
                return;
            }

            this.verifying = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route('otp.verify') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        email: '{{ $user?->email }}',
                        otp: this.getOtpString(),
                        action: this.action
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.verified = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });

                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect_url || this.redirectUrl;
                    }, 1500);
                } else {
                    this.attemptsLeft--;
                    this.errorMessage = data.message || 'Invalid verification code';
                    this.otpDigits = ['', '', '', '', '', ''];
                    this.$nextTick(() => {
                        this.focusFirstInput();
                    });
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.verifying = false;
            }
        }
    };
}
</script>
