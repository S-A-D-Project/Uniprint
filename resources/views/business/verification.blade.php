@extends('layouts.public')

@section('title', 'Business Verification')

@section('content')
<div class="min-h-screen bg-background">
    <main class="container mx-auto px-4 py-10">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Verify your print business</h1>
                <p class="text-gray-600 mt-1">Submit proof (e.g., business permit). An admin will review and approve your enterprise.</p>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-success/10 border border-success/20 rounded-lg text-success text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive text-sm">
                    <div class="font-semibold mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-muted-foreground">Enterprise</div>
                        <div class="text-lg font-semibold">{{ $enterprise->name ?? 'Enterprise' }}</div>
                    </div>
                    <div>
                        @php
                            $isVerified = isset($enterprise->is_verified) ? (bool) $enterprise->is_verified : true;
                            $hasProofColumns = \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_document_path')
                                && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_submitted_at');
                            $hasSubmittedProof = $hasProofColumns
                                && !empty($enterprise->verification_document_path)
                                && !empty($enterprise->verification_submitted_at);
                        @endphp
                        @if($isVerified)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/20">Verified</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-warning/10 text-warning border border-warning/20">Pending</span>
                        @endif
                    </div>
                </div>

                @if($hasProofColumns)
                    <div class="mt-4 p-4 bg-secondary/30 rounded-lg">
                        <div class="text-sm font-semibold">Verification status</div>
                        <div class="text-sm text-muted-foreground mt-1">
                            @if($hasSubmittedProof)
                                We already received a verification document from you.
                            @else
                                No verification document submitted yet.
                            @endif
                        </div>

                        @if($hasSubmittedProof)
                            <div class="mt-2 text-xs text-muted-foreground">
                                <div><span class="font-medium">Submitted:</span> {{ \Illuminate\Support\Carbon::parse($enterprise->verification_submitted_at)->format('M d, Y h:i A') }}</div>
                                <div>
                                    <span class="font-medium">Current file:</span>
                                    <a class="underline hover:no-underline" href="{{ asset('storage/' . ltrim($enterprise->verification_document_path, '/')) }}" target="_blank" rel="noopener">View</a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-4 p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm font-semibold">What to upload</div>
                    <div class="text-sm text-muted-foreground mt-1">
                        A clear photo or PDF of your business permit. Make sure the business name is readable.
                    </div>
                </div>

                <form method="POST" action="{{ route('business.verification.store') }}" enctype="multipart/form-data" class="space-y-4 mt-5" data-up-button-loader>
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="verification_document">Verification Document</label>
                        <input id="verification_document" name="verification_document" type="file" required accept="image/*,.pdf"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    @if(\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_notes'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="verification_notes">Notes (optional)</label>
                            <textarea id="verification_notes" name="verification_notes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">{{ old('verification_notes', $enterprise->verification_notes ?? '') }}</textarea>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-primary-foreground font-semibold hover:bg-primary/90 transition-colors" data-up-loading-text="Submitting…">
                            @if(isset($hasSubmittedProof) && $hasSubmittedProof)
                                Replace proof
                            @else
                                Submit proof
                            @endif
                        </button>
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-input bg-background hover:bg-secondary transition-colors font-semibold">
                            Back to Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
