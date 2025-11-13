# Real-time Notification System Interface Design

## Overview

This document specifies the design for the real-time notification system using Pusher integration, including visual feedback for connection states, notification delivery, and user interaction patterns.

## 1. Notification Architecture

### 1.1 Notification Types

#### System Notifications
```
Connection Status:
- Connected: Green indicator, "Connected to UniPrint"
- Disconnected: Red indicator, "Connection lost"
- Reconnecting: Yellow indicator, "Reconnecting..."

Order Updates:
- New Order: Blue notification, "New order received"
- Status Change: Orange notification, "Order status updated"
- Completion: Green notification, "Order completed"

Message Notifications:
- New Message: Purple notification, "New message from [User]"
- File Received: Blue notification, "File received from [User]"
- Typing: Subtle indicator, "[User] is typing..."
```

#### User Action Notifications
```
Success Actions:
- File Upload: "File uploaded successfully"
- Message Sent: "Message delivered"
- Order Placed: "Order placed successfully"

Error Actions:
- Upload Failed: "File upload failed"
- Connection Error: "Unable to send message"
- Validation Error: "Please check your input"
```

### 1.2 Notification Hierarchy

#### Priority Levels
```
Critical (Red):
- System errors
- Payment failures
- Security alerts

High (Orange):
- Order deadlines
- Customer complaints
- System warnings

Medium (Blue):
- New orders
- Status updates
- File uploads

Low (Gray):
- General messages
- System information
- Tips and suggestions
```

## 2. Visual Design Specifications

### 2.1 Toast Notifications

#### Desktop Toast Design
```
┌─────────────────────────────────────────────────────────────┐
│ [🔔] New message from PrintShop Pro              [×]        │
│                                                             │
│ "Your business cards are ready for review.                 │
│ Please check the attached preview."                         │
│                                                             │
│ [View Message] [Mark as Read]                    2 min ago  │
└─────────────────────────────────────────────────────────────┘

Specifications:
- Width: 400px (desktop), 90vw (mobile)
- Max Height: 200px
- Border Radius: 12px
- Shadow: 0 8px 32px rgba(0, 0, 0, 0.12)
- Animation: Slide in from top-right
- Duration: 5 seconds (auto-dismiss)
- Position: Fixed, top-right corner
```

#### Mobile Toast Design
```
┌─────────────────────────────────────────────────────────────┐
│ [🔔] New message                                   [×]      │
│ PrintShop Pro: "Your business cards are ready..."          │
│ [View] [Dismiss]                              2 min ago    │
└─────────────────────────────────────────────────────────────┘

Specifications:
- Width: 100vw - 32px
- Height: Auto (min 64px)
- Position: Fixed, top of screen
- Animation: Slide down from top
- Swipe to dismiss: Left or right
```

### 2.2 Notification Badge System

#### Badge Design
```
Notification Badge:
┌─────────────────┐
│ [🔔] [●5]       │  <- Red circle with count
│                 │
│ Messages        │
└─────────────────┘

Badge Specifications:
- Size: 20px diameter (minimum)
- Background: #dc2626 (red)
- Text: White, 12px, bold
- Position: Top-right of parent element
- Offset: -8px top, -8px right
- Animation: Scale in when appearing
- Max Count: 99+ for counts over 99
```

#### Badge States
```css
/* Default Badge */
.notification-badge {
  background: #dc2626;
  color: white;
  border-radius: 50%;
  font-size: 12px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: -8px;
  right: -8px;
}

/* Pulsing Animation for New Notifications */
.notification-badge.new {
  animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* Different Priority Colors */
.badge-critical { background: #dc2626; } /* Red */
.badge-high { background: #ea580c; }     /* Orange */
.badge-medium { background: #2563eb; }   /* Blue */
.badge-low { background: #6b7280; }      /* Gray */
```

### 2.3 In-App Notification Panel

#### Notification Center Design
```
┌─────────────────────────────────────────────────────────────┐
│                    Notifications (3)                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [🔔] New order #12345                          [●] 5m ago   │
│ Customer: John Doe                                          │
│ 500 business cards, full color                              │
│ [View Order] [Start Chat]                                   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [💬] Message from Sarah Johnson               [●] 12m ago   │
│ "Can we change the delivery date?"                          │
│ [Reply] [View Chat]                                         │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [✅] Order #12340 completed                      2h ago     │
│ Ready for pickup at PrintShop Pro                          │
│ [View Details] [Rate Service]                               │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                    [View All] [Mark All Read]               │
└─────────────────────────────────────────────────────────────┘

Panel Specifications:
- Width: 400px (desktop), 100vw (mobile)
- Max Height: 600px
- Position: Dropdown from notification icon
- Background: White with subtle shadow
- Scroll: Vertical when content exceeds height
- Animation: Fade in with scale from top-right
```

## 3. Real-time Connection Indicators

### 3.1 Connection Status Bar

#### Status Bar Design
```
Desktop Status Bar (Header):
┌─────────────────────────────────────────────────────────────┐
│ UniPrint                    [🟢] Connected • 127 online     │
└─────────────────────────────────────────────────────────────┘

Mobile Status Bar (Below header):
┌─────────────────────────────────────────────────────────────┐
│ [🟢] Connected                                              │
└─────────────────────────────────────────────────────────────┘

Status Indicators:
🟢 Connected    - Solid green circle
🟡 Connecting   - Animated yellow circle (pulsing)
🔴 Disconnected - Solid red circle
🔵 Reconnecting - Animated blue circle (spinning)
```

#### Detailed Connection Modal
```
┌─────────────────────────────────────────────────────────────┐
│                    Connection Details                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Status: 🟢 Connected                                        │
│ Server: Pusher AP1 (Singapore)                             │
│ Latency: 45ms                                               │
│ Connected Since: 2:34 PM                                    │
│                                                             │
│ Active Features:                                            │
│ ✅ Real-time messaging                                      │
│ ✅ Live notifications                                       │
│ ✅ Presence tracking                                        │
│ ✅ File sharing                                             │
│                                                             │
│ Statistics:                                                 │
│ Messages Sent: 23                                           │
│ Messages Received: 31                                       │
│ Uptime: 99.8%                                               │
│                                                             │
│                          [Reconnect] [Close]                │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 User Presence Indicators

#### Online Status Design
```
User Avatar with Presence:
┌─────────────────┐
│  [👤]  [🟢]     │  <- Avatar with status dot
│  Sarah Johnson  │
│  "Online now"   │
└─────────────────┘

Presence States:
🟢 Online (Active < 5 min)     - Green dot
🟡 Away (Active 5-30 min)      - Yellow dot
🔴 Busy (Do not disturb)       - Red dot
⚫ Offline (Active > 30 min)   - Gray dot
🟣 Typing (Real-time)          - Purple dot (animated)

Status Text:
- Online: "Active now"
- Away: "Away"
- Busy: "Do not disturb"
- Offline: "Last seen 2h ago"
- Typing: "Typing..."
```

#### Presence List Component
```
┌─────────────────────────────────────────────────────────────┐
│                     Online Users (8)                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 🟢 Sarah Johnson        PrintShop Pro      Active now       │
│ 🟢 Mike Chen           QuickPrint Ltd      Active now       │
│ 🟡 Lisa Wong           Design Studio       Away (10m)       │
│ 🟢 Tom Wilson          FastPrint Co        Active now       │
│ 🟡 Anna Davis          Creative Prints     Away (15m)       │
│ 🔴 James Miller        ProPrint Services   Busy             │
│ 🟢 Emma Thompson       PrintMaster Inc     Active now       │
│ ⚫ David Lee           QuickCopy Shop      Offline (2h)     │
│                                                             │
│                         [Show All (24)]                    │
└─────────────────────────────────────────────────────────────┘
```

## 4. Chat Interface Real-time Features

### 4.1 Typing Indicators

#### Typing Animation Design
```
Single User Typing:
┌─────────────────────────────────────────────────────────────┐
│ [👤] Sarah is typing ●●●                                    │
└─────────────────────────────────────────────────────────────┘

Multiple Users Typing:
┌─────────────────────────────────────────────────────────────┐
│ [👤👤] Sarah and Mike are typing ●●●                        │
└─────────────────────────────────────────────────────────────┘

Many Users Typing:
┌─────────────────────────────────────────────────────────────┐
│ [👥] Sarah and 3 others are typing ●●●                      │
└─────────────────────────────────────────────────────────────┘

Animation Sequence:
Frame 1: ●○○ (0.0s)
Frame 2: ○●○ (0.5s)
Frame 3: ○○● (1.0s)
Frame 4: ○○○ (1.5s)
Repeat cycle every 1.5 seconds
```

#### Typing Indicator CSS
```css
.typing-indicator {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  background: rgba(139, 92, 246, 0.1);
  border-radius: 20px;
  margin: 8px 0;
}

.typing-dots {
  display: flex;
  gap: 4px;
  margin-left: 8px;
}

.typing-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #8b5cf6;
  animation: typingDot 1.5s infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.5s; }
.typing-dot:nth-child(3) { animation-delay: 1.0s; }

@keyframes typingDot {
  0%, 60%, 100% { opacity: 0.3; transform: scale(1); }
  30% { opacity: 1; transform: scale(1.2); }
}
```

### 4.2 Message Status Indicators

#### Message State Design
```
Message Status Icons (Sent messages):
┌─────────────────────────────────────────────────────────────┐
│                                    "Hello there!" [📤]      │
│                                         2:34 PM             │
└─────────────────────────────────────────────────────────────┘

Status Icons:
📤 Sending     - Gray, animated (rotating)
✓  Sent        - Gray, static
✓✓ Delivered   - Blue, static  
✓✓ Read        - Green, static
❌ Failed      - Red, with retry button

Icon Specifications:
- Size: 16x16px
- Position: Bottom-right of message bubble
- Animation: Smooth transition between states
- Tooltip: Detailed status on hover
```

#### Read Receipt System
```css
.message-status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  margin-left: 8px;
}

.status-icon {
  width: 16px;
  height: 16px;
  transition: all 200ms ease;
}

.status-sending { color: #6b7280; animation: spin 1s linear infinite; }
.status-sent { color: #6b7280; }
.status-delivered { color: #2563eb; }
.status-read { color: #059669; }
.status-failed { color: #dc2626; cursor: pointer; }

/* Hover tooltip */
.message-status:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
}
```

## 5. Notification Behavior Patterns

### 5.1 Notification Timing

#### Auto-dismiss Rules
```
Toast Notifications:
- Success messages: 3 seconds
- Information: 5 seconds  
- Warnings: 8 seconds
- Errors: Manual dismiss only

Badge Updates:
- Immediate: New messages, orders
- Batched: Status updates (every 30s)
- Delayed: Low priority (every 5 minutes)

Sound Notifications:
- New message: Subtle chime
- New order: Distinctive tone
- Error: Alert sound
- Success: Confirmation beep
```

#### Notification Grouping
```
Message Grouping:
- Same sender within 5 minutes: Group together
- Show count: "3 new messages from Sarah"
- Expand on click: Show individual messages

Order Grouping:
- Same customer: Group by customer
- Same day: Group by time period
- Show summary: "5 new orders today"
```

### 5.2 User Preferences

#### Notification Settings Panel
```
┌─────────────────────────────────────────────────────────────┐
│                  Notification Preferences                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 🔔 Push Notifications                          [●] Enabled  │
│                                                             │
│ Message Notifications:                                      │
│ ☑️ New messages                                             │
│ ☑️ File attachments                                         │
│ ☐ Typing indicators                                         │
│                                                             │
│ Order Notifications:                                        │
│ ☑️ New orders                                               │
│ ☑️ Status updates                                           │
│ ☑️ Completion alerts                                        │
│ ☐ Payment reminders                                         │
│                                                             │
│ System Notifications:                                       │
│ ☑️ Connection status                                        │
│ ☐ Maintenance alerts                                        │
│ ☐ Feature updates                                           │
│                                                             │
│ Quiet Hours:                                                │
│ From: [10:00 PM] To: [8:00 AM]          [●] Enabled        │
│                                                             │
│ Sound Settings:                                             │
│ Volume: [████████░░] 80%                                    │
│ ☑️ Play sounds                                              │
│ ☐ Vibrate (mobile)                                          │
│                                                             │
│                    [Save Preferences] [Reset]               │
└─────────────────────────────────────────────────────────────┘
```

## 6. Error State Handling

### 6.1 Connection Error Notifications

#### Connection Lost Banner
```
┌─────────────────────────────────────────────────────────────┐
│ ⚠️ Connection lost. Messages may be delayed.   [Retry Now]  │
└─────────────────────────────────────────────────────────────┘

Specifications:
- Background: Orange (#fed7aa)
- Text: Dark orange (#9a3412)
- Position: Top of page, below header
- Animation: Slide down from top
- Auto-hide: When connection restored
- Manual dismiss: X button on right
```

#### Offline Mode Indicator
```
┌─────────────────────────────────────────────────────────────┐
│ 📱 You're offline. Changes will sync when reconnected.      │
└─────────────────────────────────────────────────────────────┘

Features:
- Persistent banner until online
- Queue pending actions
- Show sync status when reconnected
- Disable real-time features gracefully
```

### 6.2 Failed Notification Handling

#### Retry Mechanism
```
Failed Notification Display:
┌─────────────────────────────────────────────────────────────┐
│ ❌ Failed to send notification                              │
│ "New order from John Doe"                                   │
│ [Retry] [Dismiss] [View Details]                            │
└─────────────────────────────────────────────────────────────┘

Retry Logic:
- Immediate retry: 1 attempt
- Exponential backoff: 2s, 4s, 8s, 16s
- Max retries: 5 attempts
- Fallback: Store in local queue
- Manual retry: Always available
```

This comprehensive real-time notification system design ensures users stay informed about important events while maintaining a clean, intuitive interface that doesn't overwhelm with excessive notifications.
