# UniPrint Style Guide & Component Library

## Overview

This document establishes the visual design system and component library for the UniPrint Pusher integration, ensuring consistent branding and user experience across all interfaces.

## 1. Brand Identity

### 1.1 Logo Usage
```
Primary Logo:
- UniPrint wordmark with print icon
- Minimum size: 120px width
- Clear space: 1x logo height on all sides
- Backgrounds: White, light gray, or brand colors only

Logo Variations:
- Full color (primary)
- Single color (white/black)
- Icon only (for small spaces)
- Horizontal layout (for headers)
```

### 1.2 Color System

#### Primary Palette
```css
/* Brand Colors */
--uniprint-blue: #2563eb;      /* Primary brand color */
--uniprint-blue-dark: #1d4ed8; /* Hover states */
--uniprint-blue-light: #dbeafe; /* Backgrounds */

/* Supporting Colors */
--uniprint-gray: #64748b;      /* Text, borders */
--uniprint-gray-light: #f1f5f9; /* Subtle backgrounds */
--uniprint-gray-dark: #334155;  /* Headings */
```

#### Semantic Colors
```css
/* Status Colors */
--success: #059669;    /* Confirmations, success states */
--warning: #d97706;    /* Warnings, pending states */
--error: #dc2626;      /* Errors, destructive actions */
--info: #0891b2;       /* Information, neutral alerts */

/* Real-time Status */
--online: #10b981;     /* User online */
--away: #f59e0b;       /* User away */
--offline: #6b7280;    /* User offline */
--typing: #8b5cf6;     /* Typing indicator */
```

### 1.3 Typography

#### Font Stack
```css
/* Primary Font */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

/* Monospace Font */
font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
```

#### Type Scale
```css
/* Headings */
.text-4xl { font-size: 2.25rem; line-height: 2.5rem; }    /* 36px */
.text-3xl { font-size: 1.875rem; line-height: 2.25rem; }  /* 30px */
.text-2xl { font-size: 1.5rem; line-height: 2rem; }       /* 24px */
.text-xl { font-size: 1.25rem; line-height: 1.75rem; }    /* 20px */
.text-lg { font-size: 1.125rem; line-height: 1.75rem; }   /* 18px */

/* Body Text */
.text-base { font-size: 1rem; line-height: 1.5rem; }      /* 16px */
.text-sm { font-size: 0.875rem; line-height: 1.25rem; }   /* 14px */
.text-xs { font-size: 0.75rem; line-height: 1rem; }       /* 12px */
```

## 2. Component Library

### 2.1 Button Components

#### Primary Button
```html
<button class="btn btn-primary">
  <i class="icon-left"></i>
  <span>Button Text</span>
  <i class="icon-right"></i>
</button>
```

**Specifications:**
- Height: 40px (md), 32px (sm), 48px (lg)
- Padding: 12px 24px
- Border radius: 8px
- Font weight: 500
- Transition: all 200ms ease

#### Button States
```css
/* Default */
.btn-primary {
  background: var(--uniprint-blue);
  color: white;
  border: none;
}

/* Hover */
.btn-primary:hover {
  background: var(--uniprint-blue-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
}

/* Active */
.btn-primary:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
}

/* Disabled */
.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Loading */
.btn-primary.loading {
  pointer-events: none;
}
```

### 2.2 Input Components

#### Text Input
```html
<div class="input-group">
  <label class="input-label">Label Text</label>
  <input type="text" class="input-field" placeholder="Placeholder">
  <span class="input-help">Help text</span>
</div>
```

**Specifications:**
- Height: 40px
- Padding: 8px 12px
- Border: 1px solid #e2e8f0
- Border radius: 6px
- Focus: 2px solid var(--uniprint-blue)

#### Input States
```css
/* Default */
.input-field {
  border: 1px solid #e2e8f0;
  background: white;
}

/* Focus */
.input-field:focus {
  outline: none;
  border-color: var(--uniprint-blue);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Error */
.input-field.error {
  border-color: var(--error);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* Success */
.input-field.success {
  border-color: var(--success);
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}
```

### 2.3 Card Components

#### Base Card
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
    <div class="card-actions">
      <!-- Action buttons -->
    </div>
  </div>
  <div class="card-body">
    <!-- Card content -->
  </div>
  <div class="card-footer">
    <!-- Footer content -->
  </div>
</div>
```

**Specifications:**
- Background: white
- Border: 1px solid #e2e8f0
- Border radius: 12px
- Padding: 24px
- Shadow: 0 1px 3px rgba(0, 0, 0, 0.1)

### 2.4 Real-time Components

#### Chat Message Bubble
```html
<div class="message-bubble sent">
  <div class="message-content">
    <p class="message-text">Hello, how can I help you?</p>
    <div class="message-meta">
      <span class="message-time">2:34 PM</span>
      <span class="message-status">✓✓</span>
    </div>
  </div>
</div>
```

**Specifications:**
- Max width: 70% of container
- Border radius: 18px
- Padding: 12px 16px
- Sent: Blue background, white text
- Received: Gray background, dark text

#### Typing Indicator
```html
<div class="typing-indicator">
  <div class="typing-dots">
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
  </div>
  <span class="typing-text">Sarah is typing...</span>
</div>
```

#### Connection Status
```html
<div class="connection-status connected">
  <span class="status-dot"></span>
  <span class="status-text">Connected</span>
  <span class="status-details">• 127 users online</span>
</div>
```

## 3. Layout System

### 3.1 Grid System
```css
/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
}

/* Grid */
.grid {
  display: grid;
  gap: 24px;
}

.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
```

### 3.2 Spacing System
```css
/* Spacing Scale */
.p-1 { padding: 4px; }
.p-2 { padding: 8px; }
.p-3 { padding: 12px; }
.p-4 { padding: 16px; }
.p-6 { padding: 24px; }
.p-8 { padding: 32px; }

.m-1 { margin: 4px; }
.m-2 { margin: 8px; }
.m-3 { margin: 12px; }
.m-4 { margin: 16px; }
.m-6 { margin: 24px; }
.m-8 { margin: 32px; }
```

## 4. Icon System

### 4.1 Icon Library
```
Primary Icons (Bootstrap Icons):
- bi-chat: Chat/messaging
- bi-bell: Notifications
- bi-person: User/profile
- bi-gear: Settings
- bi-search: Search
- bi-plus: Add/create
- bi-x: Close/cancel
- bi-check: Confirm/success

Status Icons:
- bi-circle-fill: Online status
- bi-clock: Pending/waiting
- bi-check-circle: Success
- bi-x-circle: Error
- bi-exclamation-triangle: Warning
```

### 4.2 Icon Usage Guidelines
```css
/* Icon Sizes */
.icon-xs { width: 12px; height: 12px; }
.icon-sm { width: 16px; height: 16px; }
.icon-md { width: 20px; height: 20px; }
.icon-lg { width: 24px; height: 24px; }
.icon-xl { width: 32px; height: 32px; }

/* Icon Colors */
.icon-primary { color: var(--uniprint-blue); }
.icon-success { color: var(--success); }
.icon-warning { color: var(--warning); }
.icon-error { color: var(--error); }
.icon-muted { color: var(--uniprint-gray); }
```

## 5. Animation Guidelines

### 5.1 Transition Timing
```css
/* Standard Transitions */
.transition-fast { transition: all 150ms ease; }
.transition-normal { transition: all 200ms ease; }
.transition-slow { transition: all 300ms ease; }

/* Easing Functions */
.ease-in { transition-timing-function: ease-in; }
.ease-out { transition-timing-function: ease-out; }
.ease-in-out { transition-timing-function: ease-in-out; }
```

### 5.2 Animation Patterns
```css
/* Hover Animations */
.hover-lift:hover {
  transform: translateY(-2px);
}

.hover-scale:hover {
  transform: scale(1.05);
}

/* Loading Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
  40%, 43% { transform: translateY(-8px); }
  70% { transform: translateY(-4px); }
  90% { transform: translateY(-2px); }
}
```

## 6. Responsive Design

### 6.1 Breakpoints
```css
/* Mobile First Breakpoints */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
@media (min-width: 1536px) { /* 2xl */ }
```

### 6.2 Responsive Utilities
```css
/* Display */
.hidden { display: none; }
.block { display: block; }
.inline-block { display: inline-block; }
.flex { display: flex; }
.grid { display: grid; }

/* Responsive Display */
.sm:hidden { display: none; }
.md:block { display: block; }
.lg:flex { display: flex; }
```

This style guide provides the foundation for consistent visual design across the UniPrint Pusher integration project.
