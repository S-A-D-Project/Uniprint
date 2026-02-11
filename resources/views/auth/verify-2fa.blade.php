<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify - UniPrint</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
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

                    <form method="POST" action="{{ route('two-factor.verify.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="code" class="form-label">Verification code</label>
                            <input
                                type="text"
                                class="form-control"
                                id="code"
                                name="code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                maxlength="6"
                                required
                                value="{{ old('code') }}"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify</button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.verify.resend') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">Resend code</button>
                    </form>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
