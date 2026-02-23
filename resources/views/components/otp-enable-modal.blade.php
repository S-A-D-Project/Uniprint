{{-- OTP Enable/Disable Modal Component --}}
{{-- Usage: <x-otp-enable-modal :user="$user" /> --}}

<div x-data="otpEnableModal()" x-init="init()">
    {{-- Trigger Button --}}
    <button type="button"
            @click="open()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border hover:bg-accent transition-smooth"
            :class="{ 'bg-primary text-primary-foreground': isEnabled, 'bg-background': !isEnabled }">
        <i data-lucide="shield" class="h-5 w-5"></i>
        <span class="text-sm font-medium" x-text="isEnabled ? 'OTP Enabled' : 'Enable OTP'"></span>
    </button>

    {{-- Modal Overlay --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
         style="display: none;"
         @click="close()">
    </div>

    {{-- Modal Content --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="close()">
        <div class="bg-popover border border-border rounded-xl shadow-card-hover w-full max-w-md overflow-hidden"
             @click.stop>
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                         :class="isEnabled ? 'bg-success/10' : 'bg-primary/10'">
                        <i data-lucide="shield" class="h-5 w-5"
                           :class="isEnabled ? 'text-success' : 'text-primary'"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold" x-text="isEnabled ? 'Disable OTP' : 'Enable OTP'"></h3>
                        <p class="text-xs text-muted-foreground" x-text="isEnabled ? 'Turn off email verification' : 'Secure your account with email OTP'"></p>
                    </div>
                </div>
                <button type="button" @click="close()" class="p-2 rounded-md hover:bg-accent">
                    <i data-lucide="x" class="h-5 w-5 text-muted-foreground"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6">
                {{-- Enable OTP Form --}}
                <div x-show="!isEnabled && !showVerification" x-cloak>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="mail" class="h-8 w-8 text-primary"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Enable Email OTP</h4>
                        <p class="text-sm text-muted-foreground">
                            We'll send a one-time password to your email<br>
                            <span class="font-medium text-foreground">{{ $user->email ?? 'your email' }}</span>
                            whenever you sign in from a new device.
                        </p>
                    </div>

                    <form @submit.prevent="enableOtp()" class="space-y-4">
                        @csrf
                        <div class="flex items-start gap-3 p-3 bg-accent/50 rounded-lg">
                            <i data-lucide="info" class="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm text-muted-foreground">
                                You'll receive a 6-digit code via email. The code expires in 10 minutes.
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button"
                                    @click="close()"
                                    class="flex-1 px-4 py-2 rounded-md border border-border hover:bg-accent transition-smooth">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth"
                                    :disabled="loading"
                                    x-text="loading ? 'Sending...' : 'Send OTP'">
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Verification Step for Enabling --}}
                <div x-show="!isEnabled && showVerification" x-cloak>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="key" class="h-8 w-8 text-primary"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Verify Your Email</h4>
                        <p class="text-sm text-muted-foreground">
                            Enter the 6-digit code sent to<br>
                            <span class="font-medium text-foreground">{{ $user->email ?? 'your email' }}</span>
                        </p>
                    </div>

                    <form @submit.prevent="verifyAndEnable()" class="space-y-4">
                        @csrf
                        <div class="flex justify-center gap-2">
                            <template x-for="(digit, index) in 6" :key="index">
                                <input type="text"
                                       maxlength="1"
                                       class="w-12 h-14 text-center text-2xl font-bold rounded-lg border border-input bg-background focus:border-primary focus:ring-1 focus:ring-primary transition-smooth"
                                       x-model="otpDigits[index]"
                                       @input="handleOtpInput($event, index)"
                                       @keydown.backspace="handleOtpBackspace($event, index)"
                                       @paste="handleOtpPaste($event)"
                                       :ref="'otpInput' + index">
                            </template>
                        </div>

                        <p class="text-center text-sm text-muted-foreground">
                            Code expires in <span class="font-medium text-foreground" x-text="formatTimeLeft()"></span>
                        </p>

                        <div x-show="errorMessage" x-cloak class="p-3 bg-destructive/10 rounded-lg">
                            <p class="text-sm text-destructive" x-text="errorMessage"></p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button"
                                    @click="showVerification = false"
                                    class="flex-1 px-4 py-2 rounded-md border border-border hover:bg-accent transition-smooth">
                                Back
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth"
                                    :disabled="loading || !isOtpComplete()"
                                    x-text="loading ? 'Verifying...' : 'Enable OTP'">
                            </button>
                        </div>

                        <p class="text-center text-sm">
                            Didn't receive it?
                            <button type="button"
                                    @click="resendOtp()"
                                    class="text-primary hover:underline"
                                    :disabled="resendCooldown > 0"
                                    x-text="resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend OTP'">
                            </button>
                        </p>
                    </form>
                </div>

                {{-- Disable OTP Form --}}
                <div x-show="isEnabled" x-cloak>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="h-8 w-8 text-destructive"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Disable OTP Verification?</h4>
                        <p class="text-sm text-muted-foreground">
                            Your account will be less secure. You can re-enable it anytime.
                        </p>
                    </div>

                    <form @submit.prevent="disableOtp()" class="space-y-4">
                        @csrf
                        <div class="flex items-start gap-3 p-3 bg-destructive/5 rounded-lg">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-destructive flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm text-muted-foreground">
                                Without OTP, anyone with your password can access your account.
                            </p>
                        </div>

                        <div x-show="errorMessage" x-cloak class="p-3 bg-destructive/10 rounded-lg">
                            <p class="text-sm text-destructive" x-text="errorMessage"></p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button"
                                    @click="close()"
                                    class="flex-1 px-4 py-2 rounded-md border border-border hover:bg-accent transition-smooth">
                                Keep Enabled
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 rounded-md bg-destructive text-destructive-foreground hover:bg-destructive/90 transition-smooth"
                                    :disabled="loading"
                                    x-text="loading ? 'Disabling...' : 'Disable OTP'">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function otpEnableModal() {
    return {
        isOpen: false,
        isEnabled: {{ $user?->two_factor_email_enabled ? 'true' : 'false' }},
        loading: false,
        showVerification: false,
        otpDigits: ['', '', '', '', '', ''],
        otpExpiresAt: null,
        errorMessage: '',
        resendCooldown: 0,
        resendTimer: null,

        init() {
            this.$watch('isOpen', value => {
                if (value) {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            });
        },

        open() {
            this.isOpen = true;
            this.resetState();
        },

        close() {
            this.isOpen = false;
            this.resetState();
        },

        resetState() {
            this.showVerification = false;
            this.otpDigits = ['', '', '', '', '', ''];
            this.errorMessage = '';
            this.otpExpiresAt = null;
            if (this.resendTimer) {
                clearInterval(this.resendTimer);
                this.resendTimer = null;
            }
            this.resendCooldown = 0;
        },

        isOtpComplete() {
            return this.otpDigits.every(d => d && d.length === 1);
        },

        getOtpString() {
            return this.otpDigits.join('');
        },

        handleOtpInput(event, index) {
            const value = event.target.value;
            if (value && index < 5) {
                this.$nextTick(() => {
                    const nextInput = this.$refs['otpInput' + (index + 1)];
                    if (nextInput) nextInput.focus();
                });
            }
        },

        handleOtpBackspace(event, index) {
            if (!this.otpDigits[index] && index > 0) {
                this.$nextTick(() => {
                    const prevInput = this.$refs['otpInput' + (index - 1)];
                    if (prevInput) prevInput.focus();
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
                });
            }
        },

        formatTimeLeft() {
            if (!this.otpExpiresAt) return '10:00';
            const diff = Math.max(0, this.otpExpiresAt - Date.now());
            const mins = Math.floor(diff / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        startResendCooldown() {
            this.resendCooldown = 60;
            this.resendTimer = setInterval(() => {
                this.resendCooldown--;
                if (this.resendCooldown <= 0) {
                    clearInterval(this.resendTimer);
                    this.resendTimer = null;
                }
            }, 1000);
        },

        async enableOtp() {
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
                        email: '{{ $user?->email }}'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showVerification = true;
                    this.otpExpiresAt = Date.now() + (10 * 60 * 1000); // 10 minutes
                    this.startResendCooldown();
                    this.$nextTick(() => {
                        const firstInput = this.$refs['otpInput0'];
                        if (firstInput) firstInput.focus();
                    });
                } else {
                    this.errorMessage = data.message || 'Failed to send OTP';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async resendOtp() {
            if (this.resendCooldown > 0) return;
            await this.enableOtp();
        },

        async verifyAndEnable() {
            if (!this.isOtpComplete()) return;

            this.loading = true;
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
                        enable_two_factor: true
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.isEnabled = true;
                    this.close();
                    // Show success toast or message
                    window.dispatchEvent(new CustomEvent('otp-enabled'));
                } else {
                    this.errorMessage = data.message || 'Invalid OTP';
                    this.otpDigits = ['', '', '', '', '', ''];
                    this.$nextTick(() => {
                        const firstInput = this.$refs['otpInput0'];
                        if (firstInput) firstInput.focus();
                    });
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async disableOtp() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route('otp.disable') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.isEnabled = false;
                    this.close();
                    window.dispatchEvent(new CustomEvent('otp-disabled'));
                } else {
                    this.errorMessage = data.message || 'Failed to disable OTP';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
