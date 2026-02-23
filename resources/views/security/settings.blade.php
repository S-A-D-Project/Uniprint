@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0">Security Settings</h1>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Back</a>
            </div>

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

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">Email Two-Factor Authentication</div>
                            <div class="text-muted small">When enabled, you will receive a 6-digit code via email each time you log in.</div>
                        </div>

                        @if(!empty($twoFactorEnabled))
                            <span class="badge text-bg-success">Enabled</span>
                        @else
                            <span class="badge text-bg-secondary">Disabled</span>
                        @endif
                    </div>

                    <hr>

                    {{-- Use the OTP Enable/Disable Modal Component --}}
                    @php
                        $currentUser = auth()->user();
                    @endphp
                    <x-otp-enable-modal :user="$currentUser" />
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
