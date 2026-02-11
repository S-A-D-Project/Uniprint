@extends('layouts.business')

@section('title', 'Account Pending')
@section('page-title', 'Account Pending Verification')
@section('page-subtitle', 'Your business account is awaiting admin verification')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="h-6 w-6 text-warning"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-semibold mb-2">Your business account is pending verification</h2>
                <p class="text-muted-foreground mb-4">An admin needs to verify your enterprise before you can access business features like orders, services, pricing, and chat.</p>
                @php
                    $hasProofColumns = isset($enterprise)
                        && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_document_path')
                        && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_submitted_at');
                    $hasSubmittedProof = $hasProofColumns
                        && !empty($enterprise->verification_document_path)
                        && !empty($enterprise->verification_submitted_at);
                @endphp
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm font-medium mb-1">What you can do now</div>
                    <div class="text-sm text-muted-foreground">
                        @if($hasProofColumns)
                            @if($hasSubmittedProof)
                                We received your verification proof. Please wait for admin review.
                            @else
                                You still need to submit your verification proof so an admin can review and approve your enterprise.
                            @endif
                        @else
                            You can browse the public site, but business dashboard features are temporarily disabled until verification is completed.
                        @endif
                    </div>
                    @if($hasProofColumns)
                        <div class="mt-3 text-xs text-muted-foreground">
                            @if(isset($enterprise) && !empty($enterprise->name))
                                <div><span class="font-medium">Enterprise:</span> {{ $enterprise->name }}</div>
                            @endif
                            @if($hasSubmittedProof)
                                <div><span class="font-medium">Submitted:</span> {{ \Illuminate\Support\Carbon::parse($enterprise->verification_submitted_at)->format('M d, Y h:i A') }}</div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex gap-3 mt-5">
                    <a href="{{ route('home') }}" class="px-4 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth">Go to Home</a>
                    <a href="{{ route('business.verification') }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-smooth">
                        @if($hasProofColumns && $hasSubmittedProof)
                            Replace proof
                        @else
                            Submit proof
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-smooth">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
