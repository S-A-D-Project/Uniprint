<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Settings - UniPrint</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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

                    @if(!empty($twoFactorEnabled))
                        <form method="POST" action="{{ route('security.two-factor.disable') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger">Disable Email 2FA</button>
                        </form>
                    @else
                        <div class="d-flex flex-column gap-2">
                            <form method="POST" action="{{ route('security.two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Enable Email 2FA</button>
                            </form>

                            @if(!empty($pendingEnable))
                                <div class="alert alert-info mb-0">
                                    A confirmation code was sent to your email. Enter it below to finish enabling 2FA.
                                </div>

                                <form method="POST" action="{{ route('security.two-factor.confirm') }}" class="mt-2">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Confirmation code</label>
                                        <input type="text" id="code" name="code" class="form-control" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
                                    </div>
                                    <button type="submit" class="btn btn-success">Confirm & Enable</button>
                                </form>

                                <form method="POST" action="{{ route('security.two-factor.resend') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">Resend code</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
