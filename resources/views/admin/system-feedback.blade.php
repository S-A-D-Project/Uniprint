@extends('layouts.admin-layout')

@section('title', 'System Feedback')
@section('page-title', 'System Feedback')
@section('page-subtitle', 'Reviews and improvement suggestions submitted by users')

@section('content')
    <x-admin.card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Feedback</h3>
                    <p class="text-sm text-muted-foreground">Review and mark feedback as addressed.</p>
                </div>
            </div>
        </x-slot>

        <form method="GET" action="{{ route('admin.system-feedback') }}" class="mb-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="addressed" {{ request('status') === 'addressed' ? 'selected' : '' }}>Addressed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-muted-foreground mb-1">Category</label>
                    <select name="category" class="form-select">
                        <option value="" {{ request('category') === null || request('category') === '' ? 'selected' : '' }}>All</option>
                        <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                        <option value="bug" {{ request('category') === 'bug' ? 'selected' : '' }}>Bug / Issue</option>
                        <option value="ui" {{ request('category') === 'ui' ? 'selected' : '' }}>UI / UX</option>
                        <option value="performance" {{ request('category') === 'performance' ? 'selected' : '' }}>Performance</option>
                        <option value="feature_request" {{ request('category') === 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="admin-btn admin-btn-primary" data-up-button-loader>Filter</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/30">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Message</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-muted-foreground">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($feedback as $f)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ !empty($f->created_at) ? date('M d, Y H:i', strtotime($f->created_at)) : '' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $f->user_name ?? $f->user_id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $f->category }}</td>
                            <td class="px-4 py-3 text-sm">{{ $f->rating ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $f->subject }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="text-xs text-muted-foreground">{{ $f->message }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php $status = $f->status ?? 'new'; @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $status === 'addressed' ? 'bg-success/10 text-success' : ($status === 'reviewed' ? 'bg-muted/40 text-muted-foreground' : 'bg-warning/10 text-warning') }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if(($f->status ?? 'new') !== 'reviewed' && ($f->status ?? 'new') !== 'addressed')
                                        <form action="{{ route('admin.system-feedback.review', $f->feedback_id) }}" method="POST" data-up-global-loader>
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm" data-up-button-loader>Mark Reviewed</button>
                                        </form>
                                    @endif

                                    @if(($f->status ?? 'new') !== 'addressed')
                                        <form action="{{ route('admin.system-feedback.address', $f->feedback_id) }}" method="POST" data-up-global-loader>
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn-success admin-btn-sm" data-up-button-loader>Mark Addressed</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted-foreground">Done</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-sm text-muted-foreground" colspan="8">No feedback yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $feedback->links() }}
        </div>
    </x-admin.card>
@endsection
