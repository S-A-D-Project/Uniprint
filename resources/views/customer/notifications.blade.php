@extends('layouts.dashboard')

@section('title', 'Notifications')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Notifications</h1>
        <p class="text-muted-foreground">Stay updated on your orders</p>
    </div>

    <div class="max-w-3xl">
        @if($notifications->isNotEmpty())
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <div class="bg-card border border-border rounded-xl shadow-card p-6 {{ !$notification->is_read ? 'border-primary' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($notification->notification_type === 'status_change') bg-primary/10
                                    @elseif($notification->notification_type === 'file_upload') bg-success/10
                                    @else bg-accent/10
                                    @endif">
                                    <i data-lucide="
                                        @if($notification->notification_type === 'status_change') bell
                                        @elseif($notification->notification_type === 'file_upload') file
                                        @else message-square
                                        @endif
                                    " class="h-5 w-5 
                                        @if($notification->notification_type === 'status_change') text-primary
                                        @elseif($notification->notification_type === 'file_upload') text-success
                                        @else text-accent
                                        @endif"></i>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-semibold">{{ $notification->title }}</h3>
                                        @if(!$notification->is_read)
                                            <span class="inline-block w-2 h-2 bg-primary rounded-full"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-2">{{ $notification->message }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            
                            @if(!$notification->is_read)
                                <form action="{{ route('customer.notifications.read', $notification->notification_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-sm border border-input rounded-md hover:bg-secondary">
                                        Mark Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="bg-card border border-border rounded-xl shadow-card p-12 text-center">
                <i data-lucide="bell-off" class="h-24 w-24 mx-auto mb-4 text-muted-foreground"></i>
                <h3 class="text-xl font-bold mb-2">No Notifications</h3>
                <p class="text-muted-foreground">You're all caught up!</p>
            </div>
        @endif
    </div>
</div>
@endsection
