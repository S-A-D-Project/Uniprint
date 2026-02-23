@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h1 class="h4 mb-2">Verify your login</h1>
                    <p class="text-muted mb-4">We sent a 6-digit verification code to your email.</p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following:</div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- OTP Verification Form with 6-digit inputs --}}
                    <form method="POST" action="{{ route('two-factor.verify.submit') }}" x-data="otpForm()" @submit.prevent="submitForm()">
                        @csrf

                        {{-- 6-digit OTP Input Boxes --}}
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
                                       :ref="'otp' + index"
                                       :name="index === 0 ? 'code' : ''"
                                       autocomplete="one-time-code">
                            </template>
                        </div>

                        {{-- Hidden field for full OTP --}}
                        <input type="hidden" name="code" :value="fullOtp()">

                        {{-- Error Display --}}
                        <div x-show="error" class="alert alert-danger mb-3" x-text="error"></div>

                        {{-- Countdown Timer --}}
                        <div class="text-center mb-3">
                            <small class="text-muted">
                                Code expires in <span class="fw-semibold" :class="timeLeft < 60 ? 'text-danger' : ''" x-text="formatTime()"></span>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" :disabled="!isComplete() || loading">
                            <span x-show="!loading">Verify</span>
                            <span x-show="loading">Verifying...</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.verify.resend') }}" class="mt-3" x-data="{ cooldown: 0 }" x-init="startCooldown()">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100" :disabled="cooldown > 0">
                            <span x-show="cooldown === 0">Resend code</span>
                            <span x-show="cooldown > 0">Resend in <span x-text="cooldown"></span>s</span>
                        </button>
                    </form>

                    <script>
                        function otpForm() {
                            return {
                                otpDigits: ['', '', '', '', '', ''],
                                error: '',
                                loading: false,
                                timeLeft: 600, // 10 minutes in seconds
                                timer: null,
                                
                                init() {
                                    this.$nextTick(() => {
                                        this.$refs.otp0.focus();
                                    });
                                    this.startTimer();
                                },
                                
                                fullOtp() {
                                    return this.otpDigits.join('');
                                },
                                
                                isComplete() {
                                    return this.otpDigits.every(d => d && d.length === 1);
                                },
                                
                                handleInput(e, index) {
                                    let val = e.target.value.replace(/\D/g, '');
                                    if (val) {
                                        this.otpDigits[index] = val[0];
                                        if (index < 5) {
                                            this.$nextTick(() => this.$refs['otp' + (index + 1)].focus());
                                        } else if (this.isComplete()) {
                                            this.submitForm();
                                        }
                                    }
                                },
                                
                                handleBackspace(e, index) {
                                    if (!this.otpDigits[index] && index > 0) {
                                        this.$nextTick(() => this.$refs['otp' + (index - 1)].focus());
                                    }
                                },
                                
                                handlePaste(e) {
                                    e.preventDefault();
                                    const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                                    for (let i = 0; i < paste.length; i++) {
                                        this.otpDigits[i] = paste[i];
                                    }
                                    const nextIndex = Math.min(paste.length, 5);
                                    this.$nextTick(() => this.$refs['otp' + nextIndex].focus());
                                },
                                
                                startTimer() {
                                    this.timer = setInterval(() => {
                                        this.timeLeft--;
                                        if (this.timeLeft <= 0) {
                                            clearInterval(this.timer);
                                            this.error = 'Code has expired. Please request a new one.';
                                        }
                                    }, 1000);
                                },
                                
                                formatTime() {
                                    const mins = Math.floor(this.timeLeft / 60);
                                    const secs = this.timeLeft % 60;
                                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                                },
                                
                                async submitForm() {
                                    if (!this.isComplete()) return;
                                    this.loading = true;
                                    this.$el.submit();
                                }
                            }
                        }
                        
                        function startCooldown() {
                            return {
                                cooldown: 60,
                                startCooldown() {
                                    setInterval(() => {
                                        if (this.cooldown > 0) this.cooldown--;
                                    }, 1000);
                                }
                            }
                        }
                    </script>

                    <div class="text-center mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
