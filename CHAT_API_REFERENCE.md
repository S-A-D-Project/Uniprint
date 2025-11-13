# Chat API Reference

## Authentication

All chat API endpoints require authentication via Laravel Sanctum or session authentication.

**Headers Required:**
```
X-CSRF-TOKEN: {csrf_token}
Accept: application/json
Content-Type: application/json
```

---

## Endpoints

### Get Conversations

**GET** `/api/chat/conversations`

Get all conversations for the current user.

**Response:**
```json
{
  "success": true,
  "conversations": [
    {
      "conversation_id": "uuid",
      "participant": {
        "user_id": "uuid",
        "name": "John Doe",
        "role": "business_user"
      },
      "last_message": {
        "message_id": "uuid",
        "message_text": "Hello",
        "created_at": "2024-01-01T12:00:00Z"
      },
      "unread_count": 5,
      "last_message_at": "2024-01-01T12:00:00Z"
    }
  ],
  "user_role": "customer",
  "can_initiate_chat": true
}
```

---

### Create Conversation

**POST** `/api/chat/conversations`

Create a new conversation (customers only).

**Request Body:**
```json
{
  "business_id": "uuid"
}
```

**Response:**
```json
{
  "success": true,
  "conversation": {
    "conversation_id": "uuid",
    "customer_id": "uuid",
    "business_id": "uuid",
    "status": "active",
    "initiated_by": "customer"
  }
}
```

---

### Get Messages

**GET** `/api/chat/conversations/{conversationId}/messages`

Get messages for a conversation with pagination.

**Query Parameters:**
- `limit` (optional): Number of messages (default: 50, max: 100)
- `offset` (optional): Offset for pagination (default: 0)

**Response:**
```json
{
  "success": true,
  "messages": [
    {
      "message_id": "uuid",
      "conversation_id": "uuid",
      "sender_id": "uuid",
      "message_text": "Hello",
      "message_type": "text",
      "is_read": false,
      "created_at": "2024-01-01T12:00:00Z"
    }
  ]
}
```

---

### Send Message

**POST** `/api/chat/messages`

Send a message in a conversation.

**Request Body:**
```json
{
  "conversation_id": "uuid",
  "message_text": "Hello there!",
  "message_type": "text"
}
```

**Response:**
```json
{
  "success": true,
  "message": {
    "message_id": "uuid",
    "conversation_id": "uuid",
    "sender_id": "uuid",
    "message_text": "Hello there!",
    "message_type": "text",
    "is_read": false,
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

---

### Mark Messages as Read

**POST** `/api/chat/messages/read`

Mark messages as read.

**Request Body:**
```json
{
  "conversation_id": "uuid",
  "message_ids": ["uuid1", "uuid2"]
}
```

**Response:**
```json
{
  "success": true,
  "updated_count": 2
}
```

---

### Send Typing Indicator

**POST** `/api/chat/typing`

Broadcast typing indicator to conversation participants.

**Request Body:**
```json
{
  "conversation_id": "uuid",
  "is_typing": true
}
```

**Response:**
```json
{
  "success": true
}
```

---

### Update Online Status

**POST** `/api/chat/online-status`

Update user online status.

**Request Body:**
```json
{
  "status": "online"
}
```

**Values:** `online`, `away`, `offline`

**Response:**
```json
{
  "success": true
}
```

---

### Check Online Status

**POST** `/api/chat/online-status/check`

Check online status of multiple users.

**Request Body:**
```json
{
  "user_ids": ["uuid1", "uuid2"]
}
```

**Response:**
```json
{
  "success": true,
  "statuses": {
    "uuid1": {
      "online": true,
      "status": "online",
      "last_seen": "2024-01-01T12:00:00Z"
    },
    "uuid2": {
      "online": false,
      "status": "offline",
      "last_seen": "2024-01-01T11:00:00Z"
    }
  }
}
```

---

### Get Available Businesses

**GET** `/api/chat/available-businesses`

Get list of businesses available to chat with (customers only).

**Response:**
```json
{
  "success": true,
  "businesses": [
    {
      "user_id": "uuid",
      "name": "Print Shop",
      "email": "shop@example.com",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

---

### Pusher Authentication

**POST** `/api/chat/pusher/auth`

Authenticate Pusher private/presence channels.

**Request Body:**
```json
{
  "socket_id": "12345.67890",
  "channel_name": "presence-conversation.uuid"
}
```

**Response:**
```json
{
  "auth": "key:signature",
  "channel_data": {
    "user_id": "uuid",
    "user_info": {
      "name": "John Doe",
      "role_type": "customer"
    }
  }
}
```

---

### Cleanup Resources

**POST** `/api/chat/cleanup`

Cleanup Pusher resources when leaving chat.

**Request Body:**
```json
{
  "conversation_id": "uuid"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cleanup completed"
}
```

---

### Health Check

**GET** `/api/chat/health`

Check Pusher connection health.

**Response:**
```json
{
  "success": true,
  "pusher_available": true,
  "timestamp": "2024-01-01T12:00:00Z"
}
```

---

## Pusher Events

### Channel: `presence-conversation.{conversationId}`

**Event: `new-message`**
```json
{
  "message_id": "uuid",
  "conversation_id": "uuid",
  "sender_id": "uuid",
  "sender_name": "John Doe",
  "message_text": "Hello",
  "message_type": "text",
  "created_at": "2024-01-01T12:00:00Z"
}
```

**Event: `user-typing`**
```json
{
  "user_id": "uuid",
  "user_name": "John Doe",
  "is_typing": true
}
```

**Event: `messages-read`**
```json
{
  "message_ids": ["uuid1", "uuid2"],
  "read_by": "uuid",
  "read_at": "2024-01-01T12:00:00Z"
}
```

**Event: `pusher:member_added`**
```json
{
  "id": "uuid",
  "info": {
    "name": "John Doe",
    "role_type": "customer",
    "online_at": "2024-01-01T12:00:00Z"
  }
}
```

**Event: `pusher:member_removed`**
```json
{
  "id": "uuid"
}
```

---

## Error Responses

**400 Bad Request**
```json
{
  "error": "Invalid request",
  "message": "Required fields missing"
}
```

**401 Unauthorized**
```json
{
  "error": "Unauthorized",
  "message": "You must be logged in"
}
```

**403 Forbidden**
```json
{
  "error": "Forbidden",
  "message": "You do not have access"
}
```

**404 Not Found**
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

**500 Server Error**
```json
{
  "error": "Server error",
  "message": "An error occurred"
}
```

---

## Rate Limiting

- API endpoints: 60 requests per minute
- Typing indicators: Throttled to 1 per second client-side
- Messages: Throttled to 100ms client-side

---

## Best Practices

1. **Always handle errors** - Check response status codes
2. **Implement retries** - Use exponential backoff for failed requests
3. **Validate data** - Check message length and format before sending
4. **Clean up resources** - Call cleanup endpoint when leaving chat
5. **Monitor connection** - Listen to Pusher connection events
6. **Handle offline** - Queue messages when offline
7. **Optimize queries** - Use pagination for message history

---

## Security Notes

- All channels require authentication
- Conversation access is validated
- CSRF protection is enforced
- XSS protection via input sanitization
- Rate limiting prevents abuse
