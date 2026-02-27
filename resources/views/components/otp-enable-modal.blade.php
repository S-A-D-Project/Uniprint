{{-- Simple OTP Enable/Disable Component - Uses same design as verify-2fa --}}
<div x-data="otpEnableSimple()" x-init="init()">
    <button type="button"
            @click="open()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border hover:bg-accent transition-smooth"
            :class="{ 'bg-primary text-primary-foreground': isEnabled, 'bg-background': !isEnabled }">
        <i data-lucide="shield" class="h-5 w-5"></i>
        <span class="text-sm font-medium" x-text="isEnabled ? 'OTP Enabled' : 'Enable OTP'"></span>
    </button>

    {{-- Modal Overlay --}}
    <div x-show="isOpen"
         class="fixed inset-0 z-50 bg-black/50"
         style="display: none;"
         @click="close()">
    </div>

    {{-- Modal Content --}}
    <div x-show="isOpen"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6" @click.stop>
            
            {{-- Enable Form --}}
            <div x-show="!showVerification && !isEnabled">
                <h3 class="text-lg font-semibold text-center mb-2">Enable Email OTP</h3>
                <p class="text-muted text-center mb-4">Secure your account with email verification.</p>
                
                <div class="flex gap-3">
                    <button type="button" @click="close()" class="flex-1 btn btn-outline-secondary">Cancel</button>
                    <button type="button" @click="sendOtp()" class="flex-1 btn btn-primary" :disabled="loading">
                        <span x-show="!loading">Send Code</span>
                        <span x-show="loading">Sending...</span>
                    </button>
                </div>
            </div>

            {{-- Disable Form --}}
            <div x-show="isEnabled" class="text-center">
                <h3 class="text-lg font-semibold mb-2">Disable OTP?</h3>
                <p class="text-muted mb-4">Your account will be less secure.</p>
                
                <div class="flex gap-3">
                    <button type="button" @click="close()" class="flex-1 btn btn-outline-secondary">Keep Enabled</button>
                    <button type="button" @click="disableOtp()" class="flex-1 btn btn-danger" :disabled="loading">
                        <span x-show="!loading">Disable</span>
                        <span x-show="loading">Disabling...</span>
                    </button>
                </div>
            </div>

            {{-- Verification Form --}}
            <div x-show="showVerification && !isEnabled">
                <h3 class="text-lg font-semibold text-center mb-2">Enter Verification Code</h3>
                <p class="text-muted text-center mb-4">We sent a 6-digit code to your email.</p>

                {{-- 6-digit OTP Input --}}
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <template x-for="(digit, index) in 6" :key="index">
                        <input type="text"
                               inputmode="numeric"
                               maxlength="1"
                               class="form-control text-center fs-4 fw-bold"
                               style="width: 3rem; height: 3.5rem;"
                               x-model="otpDigits[index]"
                               @input="handleInput($event, index)"
                               @keydown.backspace="handleBackspace($event, index)"
                               @paste="handlePaste($event)"
                               :ref="'otp' + index">
                    </template>
                </div>

                <div class="text-center mb-3">
                    <small class="text-muted">Code expires in <span class="fw-semibold" x-text="formatTime()"></span></small>
                </div>

                <div x-show="error" class="alert alert-danger mb-3" x-text="error"></div>

                <button type="button" @click="verifyAndEnable()" class="btn btn-primary w-100 mb-3" :disabled="!isComplete() || loading">
                    <span x-show="!loading">Verify & Enable</span>
                    <span x-show="loading">Verifying...</span>
                </button>

                <div class="text-center">
                    <button type="button" @click="sendOtp()" class="btn btn-link btn-sm" :disabled="cooldown > 0">
                        <span x-show="cooldown === 0">Resend code</span>
                        <span x-show="cooldown > 0">Resend in <span x-text="cooldown"></span>s</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function otpEnableSimple() {
            return {
                isOpen: false,
                isEnabled: {{ $user?->two_factor_email_enabled ? 'true' : 'false' }},
                loading: false,
                showVerification: false,
                otpDigits: ['', '', '', '', '', ''],
                error: '',
                timeLeft: 600,
                cooldown: 0,
                timer: null,
                cooldownTimer: null,

                init() {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                },

                open() {
                    this.isOpen = true;
                    this.reset();
                },

                close() {
                    this.isOpen = false;
                    this.reset();
                },

                reset() {
                    this.showVerification = false;
                    this.otpDigits = ['', '', '', '', '', ''];
                    this.error = '';
                    this.loading = false;
                    this.timeLeft = 600;
                    if (this.timer) clearInterval(this.timer);
                    if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                },

                isComplete() {
                    return this.otpDigits.every(d => d && d.length === 1);
                },

                handleInput(e, index) {
                    let val = e.target.value.replace(/\D/g, '');
                    if (val) {
                        this.otpDigits[index] = val[val.length - 1];
                    }
                    if (this.otpDigits[index] && index < 5) {
                        this.$nextTick(() => this.$refs['otp' + (index + 1)].focus());
                    }
                },

                handleBackspace(e, index) {
                    if (!this.otpDigits[index] && index > 0) {
                        this.$nextTick(() => this.$refs['otp' + (index - 1)].focus());
                    }
                },

                handlePaste(e) {
                    e.preventDefault();
                    let paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                    for (let i = 0; i < paste.length; i++) {
                        this.otpDigits[i] = paste[i];
                    }
                    this.$nextTick(() => {
                        const next = Math.min(paste.length, 5);
                        this.$refs['otp' + next].focus();
                    });
                },

                formatTime() {
                    const m = Math.floor(this.timeLeft / 60);
                    const s = this.timeLeft % 60;
                    return m + ':' + (s < 10 ? '0' : '') + s;
                },

                startTimer() {
                    this.timer = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) clearInterval(this.timer);
                    }, 1000);
                },

                startCooldown() {
                    this.cooldown = 60;
                    this.cooldownTimer = setInterval(() => {
                        this.cooldown--;
                        if (this.cooldown <= 0) clearInterval(this.cooldownTimer);
                    }, 1000);
                },

                async sendOtp() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch('{{ route('otp.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ email: '{{ $user?->email }}' })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.showVerification = true;
                            this.startTimer();
                            this.startCooldown();
                            this.$nextTick(() => this.$refs.otp0.focus());
                        } else {
                            this.error = data.message || 'Failed to send code';
                        }
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async verifyAndEnable() {
                    if (!this.isComplete()) return;
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch('{{ route('otp.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                email: '{{ $user?->email }}',
                                otp: this.otpDigits.join(''),
                                enable_two_factor: true
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.isEnabled = true;
                            this.close();
                        } else {
                            this.error = data.message || 'Invalid code';
                            this.otpDigits = ['', '', '', '', '', ''];
                            this.$nextTick(() => this.$refs.otp0.focus());
                        }
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async disableOtp() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch('{{ route('otp.disable') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.isEnabled = false;
                            this.close();
                        } else {
                            this.error = data.message || 'Failed to disable';
                        }
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</div>
