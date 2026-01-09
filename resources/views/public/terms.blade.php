@extends('layouts.public')

@section('title', 'Terms & Conditions')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        <div class="bg-card border border-border rounded-xl shadow-card p-8">
            <h1 class="text-3xl font-bold mb-2">Terms & Conditions</h1>
            <p class="text-sm text-muted-foreground mb-8">Last updated: {{ date('F j, Y') }}</p>

            <div class="space-y-6 text-sm leading-7 text-foreground">
                <p>
                    These Terms & Conditions govern your use of UniPrint and the services offered through the platform.
                    By creating an account or using the platform, you agree to these terms.
                </p>

                <div>
                    <h2 class="text-lg font-semibold mb-2">1. Accounts</h2>
                    <p>
                        You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account.
                    </p>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">2. Orders and Services</h2>
                    <p>
                        UniPrint connects customers with printing enterprises. Service availability, pricing, and fulfillment times may vary by enterprise.
                    </p>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">3. Payments</h2>
                    <p>
                        Payments and fees are processed according to the checkout flow displayed at the time of purchase.
                    </p>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">4. Acceptable Use</h2>
                    <p>
                        You agree not to misuse the platform, including attempts to access systems or data that you are not authorized to access.
                    </p>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">5. Changes</h2>
                    <p>
                        We may update these Terms & Conditions from time to time. Continued use of the platform after updates constitutes acceptance.
                    </p>
                </div>

                <div class="pt-4">
                    <a href="{{ route('home') }}" class="text-primary hover:underline">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
