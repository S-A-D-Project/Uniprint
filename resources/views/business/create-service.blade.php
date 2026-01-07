@extends('layouts.business')

@section('title', 'Create Service')
@section('page-title', 'Create New Service')
@section('page-subtitle', 'Add a new service to your catalog')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <p class="text-muted-foreground">This page has been replaced.</p>
        <div class="mt-4">
            <a href="{{ route('business.services.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth">
                Go to Create Service
            </a>
        </div>
    </div>
</div>
@endsection
