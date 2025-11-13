<!-- Floating Chat Button -->
<style>
    .floating-chat-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        transition: all 0.3s ease;
    }
    
    .floating-chat-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.6);
    }
    
    .floating-chat-button .btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .floating-chat-button .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #EF4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        border: 2px solid white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .floating-chat-button .btn:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    
    @media (max-width: 768px) {
        .floating-chat-button {
            bottom: 20px;
            right: 20px;
        }
        
        .floating-chat-button .btn {
            width: 56px;
            height: 56px;
            font-size: 22px;
        }
    }
</style>

<div class="floating-chat-button">
    <a href="{{ route('chat.index') }}" class="btn btn-primary" title="Open Chat">
        <i class="bi bi-chat-dots-fill"></i>
        @if(isset($unreadCount) && $unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </a>
</div>
