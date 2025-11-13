# Real-time Chat System Setup Guide

## Overview

UniPrint now features a fully functional real-time chat system using Pusher-JS CDN for WebSocket communication between customers and businesses.

## Features

- Direct Customer-Business Communication
- Customer-Initiated Conversations
- Real-time Message Delivery
- Typing Indicators
- Online Status Tracking  
- Read Receipts
- Presence Channels
- Error Handling with Fallbacks
- Automatic Reconnection
- Resource Cleanup

## Setup Instructions

### 1. Install Pusher PHP SDK

```bash
composer require pusher/pusher-php-server
```

### 2. Get Pusher Credentials

1. Go to pusher.com and create a free account
2. Create a new Channels app
3. Get your credentials: App ID, Key, Secret, Cluster

### 3. Configure Environment

Update your .env file:

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

PUSHER_SCHEME=https
PUSHER_HOST=
PUSHER_PORT=443
```

### 4. Test the Configuration

Run this command to check if Pusher is configured correctly:

```bash
php artisan tinker
```

Then test:

```php
broadcast(new App\Events\TestEvent());
```

## Usage

### For Customers

1. Navigate to `/chat`
2. Click "Start New Chat" button
3. Select a business to chat with
4. Start messaging in real-time

### For Businesses

1. Navigate to `/business/chat`
2. View all customer conversations
3. Click on a conversation to respond
4. Messages are delivered in real-time

## API Endpoints

- GET `/api/chat/conversations` - Get all conversations
- POST `/api/chat/conversations` - Create conversation (customer only)
- GET `/api/chat/conversations/{id}/messages` - Get messages
- POST `/api/chat/messages` - Send message
- POST `/api/chat/typing` - Send typing indicator
- POST `/api/chat/online-status` - Update online status
- POST `/api/chat/pusher/auth` - Authenticate Pusher channels
- POST `/api/chat/cleanup` - Cleanup resources
- GET `/api/chat/health` - Check connection health

## Troubleshooting

### Connection Issues

Check browser console for errors. Common issues:

1. Invalid Pusher credentials - Verify .env settings
2. CORS errors - Check Pusher app settings
3. SSL errors - Ensure forceTLS is enabled

### Messages Not Delivering

1. Check Pusher dashboard for activity
2. Verify channel subscriptions in console
3. Check server logs for errors

## Security

- All channels require authentication
- Middleware validates conversation access
- Only conversation participants can subscribe
- Presence channels track active users
- Automatic cleanup on disconnect

## Performance

- Messages are throttled to 100ms
- Typing indicators throttled to 1s
- Automatic reconnection with backoff
- Connection health monitoring
- Efficient presence tracking

## Architecture

### Backend
- ChatController handles API requests
- PusherService manages broadcasting
- Conversation and ChatMessage models
- CheckChatAccess middleware for security

### Frontend
- chat-app.js for customer interface
- business/chat.blade.php for business interface
- Pusher-JS CDN 8.2.0 for WebSockets
- Presence channels for active tracking

## Support

For issues or questions, check:
- Browser console logs
- Laravel logs: storage/logs/laravel.log
- Pusher dashboard for activity

## Next Steps

1. Customize chat UI to match your brand
2. Add file attachment support
3. Implement push notifications
4. Add chat history export
5. Create chat analytics dashboard
