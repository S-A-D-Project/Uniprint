@extends('layouts.business')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Stay updated on your orders')

@section('content')
    <div class="max-w-3xl">
        @if($notifications->isNotEmpty())
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <div class="bg-card border border-border rounded-xl shadow-card p-6 {{ !$notification->is_read ? 'border-primary' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($notification->notification_type === 'deadline_warning') bg-warning/10
                                    @elseif($notification->notification_type === 'status_change') bg-primary/10
                                    @elseif($notification->notification_type === 'file_upload') bg-success/10
                                    @else bg-accent/10
                                    @endif">
                                    <i data-lucide="
                                        @if($notification->notification_type === 'deadline_warning') alert-triangle
                                        @elseif($notification->notification_type === 'status_change') bell
                                        @elseif($notification->notification_type === 'file_upload') file
                                        @else message-square
                                        @endif
                                    " class="h-5 w-5
                                        @if($notification->notification_type === 'deadline_warning') text-warning
                                        @elseif($notification->notification_type === 'status_change') text-primary
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

                                    @if(!empty($notification->purchase_order_id))
                                        <div class="mt-3">
                                            <x-ui.tooltip text="View the related order details">
                                                <a href="{{ route('business.orders.details', $notification->purchase_order_id) }}" class="text-primary hover:text-primary/80 font-medium text-sm">
                                                    View order →
                                                </a>
                                            </x-ui.tooltip>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if(!$notification->is_read)
                                <form action="{{ route('business.notifications.read', $notification->notification_id) }}" method="POST">
                                    @csrf
                                    <x-ui.tooltip text="Mark this notification as read">
                                        <button type="submit" class="px-3 py-1 text-sm border border-input rounded-md hover:bg-secondary">
                                            Mark Read
                                        </button>
                                    </x-ui.tooltip>
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
@endsection
