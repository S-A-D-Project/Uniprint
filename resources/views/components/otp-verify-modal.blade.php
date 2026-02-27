{{-- Simple OTP Verification Modal - Same design as verify-2fa --}}
<div x-data="otpVerifySimple()" x-init="init()">
    @if($showTrigger ?? true)
    <button type="button" @click="open()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth" {{ $triggerAttributes ?? '' }}>
        <i data-lucide="shield-check" class="h-5 w-5"></i>
        <span class="text-sm font-medium">{{ $triggerText ?? 'Verify with OTP' }}</span>
    </button>
    @endif

    <div x-show="isOpen" class="fixed inset-0 z-50 bg-black/50" style="display: none;" @click="close()"></div>

    <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6" @click.stop>
            
            <div x-show="!otpSent">
                <h3 class="text-lg font-semibold text-center mb-2">Verify Your Identity</h3>
                <p class="text-muted text-center mb-4">We need to verify it's really you.</p>
                <button type="button" @click="sendOtp()" class="btn btn-primary w-100" :disabled="loading">
                    <span x-show="!loading">Send Verification Code</span>
                    <span x-show="loading">Sending...</span>
                </button>
            </div>

            <div x-show="otpSent && !verified">
                <h3 class="text-lg font-semibold text-center mb-2">Enter Verification Code</h3>
                <p class="text-muted text-center mb-4">We sent a 6-digit code to {{ $user->email ?? 'your email' }}.</p>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <template x-for="(digit, index) in 6" :key="index">
                        <input type="text" inputmode="numeric" maxlength="1"
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

                <button type="button" @click="verify()" class="btn btn-primary w-100 mb-3" :disabled="!isComplete() || loading">
                    <span x-show="!loading">Verify & Continue</span>
                    <span x-show="loading">Verifying...</span>
                </button>

                <div class="text-center">
                    <button type="button" @click="sendOtp()" class="btn btn-link btn-sm" :disabled="cooldown > 0">
                        <span x-show="cooldown === 0">Resend code</span>
                        <span x-show="cooldown > 0">Resend in <span x-text="cooldown"></span>s</span>
                    </button>
                </div>
            </div>

            <div x-show="verified" class="text-center">
                <div class="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="h-8 w-8 text-success"></i>
                </div>
                <h4 class="font-semibold mb-2">Verification Successful!</h4>
                <p class="text-sm text-muted-foreground">Redirecting...</p>
            </div>

        </div>
    </div>

    <script>
        function otpVerifySimple() {
            return {
                isOpen: {{ $autoOpen ?? false ? 'true' : 'false' }},
                loading: false,
                otpSent: false,
                verified: false,
                otpDigits: ['', '', '', '', '', ''],
                error: '',
                timeLeft: 600,
                cooldown: 0,
                timer: null,
                cooldownTimer: null,
                redirectUrl: '{{ $redirectUrl ?? route('home') }}',

                init() {
                    if (this.isOpen && !this.otpSent) {
                        this.sendOtp();
                    }
                },

                open() {
                    this.isOpen = true;
                    if (!this.otpSent) {
                        this.sendOtp();
                    }
                },

                close() {
                    this.isOpen = false;
                    this.reset();
                },

                reset() {
                    this.otpSent = false;
                    this.verified = false;
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
                            this.otpSent = true;
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

                async verify() {
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
                                otp: this.otpDigits.join('')
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.verified = true;
                            setTimeout(() => {
                                window.location.href = data.redirect_url || this.redirectUrl;
                            }, 1500);
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
                }
            };
        }
    </script>
</div>
