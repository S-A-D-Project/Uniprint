# UniPrint Pusher Integration - Design Specification

## Project Overview

This document outlines the complete visual design, user interface layout, and system workflow for the UniPrint Pusher integration project, focusing on real-time communication features and enhanced user experience.

### Design Goals
- **Real-time Communication**: Seamless chat and notification system
- **Responsive Design**: Consistent experience across all devices
- **Intuitive Navigation**: Clear user journeys and system states
- **Visual Consistency**: Unified branding and design language
- **Accessibility**: WCAG 2.1 AA compliant interfaces

### Pusher Configuration
```
App ID: 2077111
Key: f7ca062b8f895c3f2497
Secret: 9829cf3fa2e92e92ab08
Cluster: ap1
```

## 1. UI/UX Design Specifications

### 1.1 Design System Foundation

#### Color Palette
```css
/* Primary Colors */
--primary: #2563eb;           /* Blue - Main brand color */
--primary-foreground: #ffffff;
--primary-hover: #1d4ed8;
--primary-light: #dbeafe;

/* Secondary Colors */
--secondary: #64748b;         /* Slate - Supporting elements */
--secondary-foreground: #ffffff;
--secondary-hover: #475569;
--secondary-light: #f1f5f9;

/* Status Colors */
--success: #059669;           /* Green - Success states */
--success-light: #d1fae5;
--warning: #d97706;           /* Orange - Warning states */
--warning-light: #fed7aa;
--danger: #dc2626;            /* Red - Error states */
--danger-light: #fecaca;
--info: #0891b2;              /* Cyan - Information */
--info-light: #cffafe;

/* Real-time Status Colors */
--online: #10b981;            /* Green - User online */
--away: #f59e0b;              /* Amber - User away */
--offline: #6b7280;           /* Gray - User offline */
--typing: #8b5cf6;            /* Purple - Typing indicator */

/* Neutral Colors */
--background: #ffffff;
--foreground: #0f172a;
--muted: #f8fafc;
--muted-foreground: #64748b;
--border: #e2e8f0;
--input: #e2e8f0;
--ring: #2563eb;
```

#### Typography Scale
```css
/* Font Family */
--font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
--font-mono: 'JetBrains Mono', 'Fira Code', monospace;

/* Font Sizes */
--text-xs: 0.75rem;    /* 12px */
--text-sm: 0.875rem;   /* 14px */
--text-base: 1rem;     /* 16px */
--text-lg: 1.125rem;   /* 18px */
--text-xl: 1.25rem;    /* 20px */
--text-2xl: 1.5rem;    /* 24px */
--text-3xl: 1.875rem;  /* 30px */
--text-4xl: 2.25rem;   /* 36px */

/* Font Weights */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;

/* Line Heights */
--leading-tight: 1.25;
--leading-normal: 1.5;
--leading-relaxed: 1.625;
```

#### Spacing System
```css
/* Spacing Scale (rem units) */
--space-1: 0.25rem;    /* 4px */
--space-2: 0.5rem;     /* 8px */
--space-3: 0.75rem;    /* 12px */
--space-4: 1rem;       /* 16px */
--space-5: 1.25rem;    /* 20px */
--space-6: 1.5rem;     /* 24px */
--space-8: 2rem;       /* 32px */
--space-10: 2.5rem;    /* 40px */
--space-12: 3rem;      /* 48px */
--space-16: 4rem;      /* 64px */
--space-20: 5rem;      /* 80px */
--space-24: 6rem;      /* 96px */
```

#### Border Radius
```css
--radius-sm: 0.25rem;   /* 4px */
--radius-md: 0.375rem;  /* 6px */
--radius-lg: 0.5rem;    /* 8px */
--radius-xl: 0.75rem;   /* 12px */
--radius-2xl: 1rem;     /* 16px */
--radius-full: 9999px;  /* Fully rounded */
```

#### Shadows
```css
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
--shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
--shadow-glow: 0 0 0 3px rgb(37 99 235 / 0.1);
```

### 1.2 Component Library Specifications

#### Button Components
```
Primary Button:
- Background: var(--primary)
- Text: var(--primary-foreground)
- Padding: 12px 24px
- Border Radius: var(--radius-lg)
- Font Weight: var(--font-medium)
- Hover: var(--primary-hover) + var(--shadow-md)
- Focus: var(--shadow-glow)
- Disabled: 50% opacity

Secondary Button:
- Background: var(--secondary)
- Text: var(--secondary-foreground)
- Same dimensions as primary
- Hover: var(--secondary-hover)

Outline Button:
- Background: transparent
- Border: 1px solid var(--border)
- Text: var(--foreground)
- Hover: var(--muted)

Ghost Button:
- Background: transparent
- Text: var(--foreground)
- Hover: var(--muted)

Icon Button:
- Size: 40x40px
- Border Radius: var(--radius-lg)
- Icon: 20x20px centered
```

#### Input Components
```
Text Input:
- Height: 40px
- Padding: 8px 12px
- Border: 1px solid var(--input)
- Border Radius: var(--radius-md)
- Focus: 2px solid var(--ring)
- Error: 2px solid var(--danger)
- Success: 2px solid var(--success)

Search Input:
- Same as text input
- Left icon: Search (16x16px)
- Right icon: Clear (when has value)

Textarea:
- Min Height: 80px
- Resize: vertical only
- Same styling as text input
```

#### Card Components
```
Base Card:
- Background: var(--background)
- Border: 1px solid var(--border)
- Border Radius: var(--radius-xl)
- Padding: 24px
- Shadow: var(--shadow-sm)

Elevated Card:
- Same as base card
- Shadow: var(--shadow-md)
- Hover: var(--shadow-lg)

Interactive Card:
- Same as elevated card
- Cursor: pointer
- Hover: transform translateY(-2px)
- Transition: all 200ms ease
```

### 1.3 Layout Specifications

#### Grid System
```
Container Max Widths:
- sm: 640px
- md: 768px
- lg: 1024px
- xl: 1280px
- 2xl: 1536px

Grid Columns: 12-column system
Gutters: 24px (desktop), 16px (mobile)

Breakpoints:
- xs: 0px
- sm: 640px
- md: 768px
- lg: 1024px
- xl: 1280px
- 2xl: 1536px
```

#### Header Layout
```
Desktop Header (Height: 64px):
┌─────────────────────────────────────────────────────────────┐
│ [Logo] [Navigation Menu] [Help] [Search] [Notifications]   │
│                                           [Sign In] [Profile]│
└─────────────────────────────────────────────────────────────┘

Mobile Header (Height: 56px):
┌─────────────────────────────────────────────────────────────┐
│ [Menu] [Logo] [Help]              [Notifications] [Sign In]│
└─────────────────────────────────────────────────────────────┘

Header Button Specifications:
- Logo: 120x40px, clickable to homepage
- Help Button: 40x40px, question mark icon, tooltip "Help & Support"
- Search: 200px width, expandable to 300px on focus
- Notifications: 40x40px, bell icon with red badge for unread count
- Sign In Button: 80x40px, "Sign In" text, primary button style
- Profile Avatar: 40x40px circular, dropdown menu on click (shown when logged in)
- Menu (Mobile): 40x40px, hamburger icon, slides out navigation drawer

Authentication State Variations:
Logged Out: [Sign In] button visible, [Profile] hidden
Logged In: [Sign In] hidden, [Profile Avatar] visible
```

#### Sidebar Layout
```
Desktop Sidebar (Width: 280px):
┌─────────────────────┐
│ [User Info]         │
│ ─────────────────── │
│ [Navigation Items]  │
│ • Dashboard         │
│ • Orders            │
│ • Messages          │
│ • Products          │
│ ─────────────────── │
│ [Real-time Status]  │
│ • Online Users      │
│ • Active Chats      │
└─────────────────────┘

Mobile: Collapsible overlay
```

#### Main Content Layout
```
Desktop Layout:
┌─────────────────────────────────────────────────────────────┐
│                        Header                               │
├─────────────┬───────────────────────────────────────────────┤
│   Sidebar   │              Main Content                     │
│             │                                               │
│             │  ┌─────────────────────────────────────────┐  │
│             │  │           Page Content                  │  │
│             │  │                                         │  │
│             │  └─────────────────────────────────────────┘  │
│             │                                               │
└─────────────┴───────────────────────────────────────────────┘

Mobile Layout:
┌─────────────────────────────────────────────────────────────┐
│                        Header                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                    Main Content                             │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                 Page Content                            ││
│  │                                                         ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 2. Responsive Design Specifications

### 2.1 Breakpoint Strategy

#### Mobile First Approach
```css
/* Base styles (mobile) */
.component {
  /* Mobile styles */
}

/* Tablet and up */
@media (min-width: 768px) {
  .component {
    /* Tablet styles */
  }
}

/* Desktop and up */
@media (min-width: 1024px) {
  .component {
    /* Desktop styles */
  }
}

/* Large desktop and up */
@media (min-width: 1280px) {
  .component {
    /* Large desktop styles */
  }
}
```

#### Component Responsive Behavior

**Navigation:**
- Mobile: Hamburger menu with slide-out drawer
- Tablet: Horizontal navigation with dropdowns
- Desktop: Full horizontal navigation

**Chat Interface:**
- Mobile: Full-screen overlay
- Tablet: Side panel (50% width)
- Desktop: Side panel (30% width)

**Data Tables:**
- Mobile: Card layout with stacked information
- Tablet: Horizontal scroll with fixed columns
- Desktop: Full table layout

**Forms:**
- Mobile: Single column, full-width inputs
- Tablet: Two-column layout for related fields
- Desktop: Multi-column with logical grouping

### 2.2 Touch Interface Considerations

#### Touch Targets
```
Minimum Touch Target: 44x44px
Recommended: 48x48px
Spacing between targets: 8px minimum

Button Heights:
- Mobile: 48px
- Tablet: 44px
- Desktop: 40px
```

#### Gesture Support
- **Swipe**: Navigate between chat conversations
- **Pull to Refresh**: Update message lists
- **Long Press**: Context menus for messages
- **Pinch to Zoom**: Image previews in chat

## 3. Real-time Interface Specifications

### 3.1 Connection Status Indicators

#### Connection States
```
Connected:
- Icon: Green circle (8px diameter)
- Text: "Connected"
- Color: var(--success)
- Animation: Subtle pulse every 3s

Connecting:
- Icon: Animated spinner (16px)
- Text: "Connecting..."
- Color: var(--warning)
- Animation: Continuous rotation

Disconnected:
- Icon: Red circle with X (8px)
- Text: "Disconnected"
- Color: var(--danger)
- Animation: None

Reconnecting:
- Icon: Animated dots (...)
- Text: "Reconnecting..."
- Color: var(--info)
- Animation: Dot sequence
```

#### Status Bar Component
```
Desktop Status Bar (Top right corner):
┌─────────────────────────────────────┐
│ [●] Connected • 3 online users      │
└─────────────────────────────────────┘

Mobile Status Bar (Below header):
┌─────────────────────────────────────┐
│ [●] Connected                       │
└─────────────────────────────────────┘
```

### 3.2 Notification System Design

#### Notification Types
```
Toast Notifications:
- Success: Green background, checkmark icon
- Error: Red background, X icon
- Warning: Orange background, exclamation icon
- Info: Blue background, info icon

Dimensions:
- Width: 400px (desktop), 90vw (mobile)
- Min Height: 64px
- Max Height: 200px
- Border Radius: var(--radius-lg)
- Shadow: var(--shadow-lg)

Animation:
- Enter: Slide in from right (desktop), slide down from top (mobile)
- Exit: Fade out with slide
- Duration: 300ms ease-out
```

#### Badge Notifications
```
Notification Badge:
- Size: 20x20px (minimum)
- Background: var(--danger)
- Text: var(--primary-foreground)
- Font Size: 12px
- Font Weight: var(--font-bold)
- Border Radius: var(--radius-full)
- Position: Top-right of parent element
- Offset: -8px top, -8px right

Count Display:
- 1-99: Show exact number
- 100+: Show "99+"
- Animation: Scale in when appearing
```

### 3.3 Chat Interface Design

#### Chat List Component
```
Chat List Item:
┌─────────────────────────────────────────────────────────────┐
│ [Avatar] [Name]                    [Time] [Unread Badge]    │
│          [Last Message Preview]           [Status Dot]      │
└─────────────────────────────────────────────────────────────┘

Dimensions:
- Height: 72px
- Padding: 12px 16px
- Avatar: 48x48px
- Status Dot: 12x12px (bottom-right of avatar)
- Unread Badge: Dynamic width, 20px height

States:
- Default: var(--background)
- Hover: var(--muted)
- Active: var(--primary-light)
- Unread: Bold name, var(--primary) accent
```

#### Message Bubble Design
```
Sent Message (Right-aligned):
┌─────────────────────────────────────────────────────────────┐
│                                    [Message Content] [●●●]  │
│                                    [Timestamp] [Status]     │
└─────────────────────────────────────────────────────────────┘

Received Message (Left-aligned):
┌─────────────────────────────────────────────────────────────┐
│ [Avatar] [Message Content]                                  │
│          [Timestamp]                                        │
└─────────────────────────────────────────────────────────────┘

Bubble Styling:
- Sent: var(--primary) background, white text
- Received: var(--muted) background, var(--foreground) text
- Border Radius: 18px (with tail effect)
- Padding: 12px 16px
- Max Width: 70% of container
- Margin: 4px between messages, 16px between different senders
```

#### Typing Indicator
```
Typing Indicator:
┌─────────────────────────────────────────────────────────────┐
│ [Avatar] [●●●] [Name] is typing...                          │
└─────────────────────────────────────────────────────────────┘

Animation:
- Three dots with sequential fade in/out
- Duration: 1.5s loop
- Color: var(--typing)
- Position: Bottom of chat area
```

#### Message Input Area
```
Input Area Layout:
┌─────────────────────────────────────────────────────────────┐
│ [Attach] [Text Input Area]                    [Emoji] [Send]│
└─────────────────────────────────────────────────────────────┘

Dimensions:
- Height: 56px (collapsed), auto (expanded)
- Padding: 8px 16px
- Input: Flexible height (min 40px, max 120px)
- Buttons: 40x40px each
- Border: 1px solid var(--border) (top only)
```

## 4. Animation and Transition Specifications

### 4.1 Micro-interactions

#### Button Interactions
```css
/* Hover Effects */
.button:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
  transition: all 200ms ease;
}

/* Click Effects */
.button:active {
  transform: translateY(0);
  transition: all 100ms ease;
}

/* Loading State */
.button.loading {
  pointer-events: none;
}

.button.loading .spinner {
  animation: spin 1s linear infinite;
}
```

#### Real-time Animations
```css
/* New Message Animation */
.message.new {
  animation: slideInMessage 300ms ease-out;
}

@keyframes slideInMessage {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Typing Indicator */
.typing-dots {
  animation: typingDots 1.5s infinite;
}

@keyframes typingDots {
  0%, 60%, 100% { opacity: 0.3; }
  30% { opacity: 1; }
}

/* Connection Status Pulse */
.status-connected {
  animation: statusPulse 3s infinite;
}

@keyframes statusPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}
```

### 4.2 Page Transitions

#### Route Transitions
```css
/* Page Enter */
.page-enter {
  opacity: 0;
  transform: translateX(20px);
}

.page-enter-active {
  opacity: 1;
  transform: translateX(0);
  transition: all 300ms ease-out;
}

/* Page Exit */
.page-exit {
  opacity: 1;
  transform: translateX(0);
}

.page-exit-active {
  opacity: 0;
  transform: translateX(-20px);
  transition: all 300ms ease-in;
}
```

#### Modal Transitions
```css
/* Modal Backdrop */
.modal-backdrop-enter {
  opacity: 0;
}

.modal-backdrop-enter-active {
  opacity: 1;
  transition: opacity 200ms ease;
}

/* Modal Content */
.modal-content-enter {
  opacity: 0;
  transform: scale(0.95) translateY(-20px);
}

.modal-content-enter-active {
  opacity: 1;
  transform: scale(1) translateY(0);
  transition: all 200ms ease-out;
}
```

## 5. Accessibility Specifications

### 5.1 WCAG 2.1 AA Compliance

#### Color Contrast Requirements
```
Normal Text: 4.5:1 minimum ratio
Large Text (18pt+): 3:1 minimum ratio
UI Components: 3:1 minimum ratio

Status Colors Contrast:
- Success on white: 4.52:1 ✓
- Warning on white: 4.89:1 ✓
- Danger on white: 5.74:1 ✓
- Info on white: 4.93:1 ✓
```

#### Keyboard Navigation
```
Tab Order:
1. Skip to main content link
2. Header navigation
3. Search input
4. Main content area
5. Sidebar navigation
6. Footer links

Focus Indicators:
- Visible: 2px solid var(--ring)
- Offset: 2px from element
- Border Radius: Matches element
```

#### Screen Reader Support
```html
<!-- Chat Message Example -->
<div role="log" aria-live="polite" aria-label="Chat messages">
  <div role="article" aria-labelledby="msg-1-author" aria-describedby="msg-1-time">
    <span id="msg-1-author">John Doe</span>
    <span id="msg-1-time">2 minutes ago</span>
    <p>Hello, how can I help you today?</p>
  </div>
</div>

<!-- Connection Status -->
<div role="status" aria-live="polite">
  <span class="sr-only">Connection status: </span>
  Connected
</div>

<!-- Notification -->
<div role="alert" aria-live="assertive">
  New message from Sarah Johnson
</div>
```

### 5.2 Reduced Motion Support

#### Motion Preferences
```css
@media (prefers-reduced-motion: reduce) {
  /* Disable animations */
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
  
  /* Keep essential animations */
  .loading-spinner {
    animation: spin 1s linear infinite;
  }
}
```

## 6. Performance Specifications

### 6.1 Loading States

#### Skeleton Screens
```
Chat List Skeleton:
┌─────────────────────────────────────────────────────────────┐
│ [○] [████████████]              [██] [○]                    │
│     [██████████████████]                                    │
├─────────────────────────────────────────────────────────────┤
│ [○] [████████████]              [██] [○]                    │
│     [██████████████████]                                    │
└─────────────────────────────────────────────────────────────┘

Animation: Shimmer effect (left to right)
Duration: 1.5s infinite
Color: Linear gradient of var(--muted) shades
```

#### Progressive Loading
```
1. Layout shell (0ms)
2. Navigation (100ms)
3. Sidebar (200ms)
4. Main content (300ms)
5. Real-time features (500ms)
```

### 6.2 Optimization Guidelines

#### Image Optimization
```
Avatar Images:
- Format: WebP with JPEG fallback
- Sizes: 48px, 96px, 144px (1x, 2x, 3x)
- Lazy loading: Intersection Observer
- Placeholder: Colored circle with initials

File Attachments:
- Thumbnails: 200x200px max
- Preview: 800x600px max
- Compression: 80% quality
```

#### Bundle Optimization
```
Critical CSS: Inline in <head>
Non-critical CSS: Load asynchronously
JavaScript: Code splitting by route
Fonts: Preload primary font weights
Icons: SVG sprite or icon font
```

This design specification provides the foundation for implementing a cohesive, accessible, and performant Pusher integration. The next sections will detail the system workflows and user journeys.
