# Customer Design Consistency Guide

## Overview

This guide ensures all customer-facing pages in the UniPrint application maintain consistent design patterns, navigation, and user experience across all tabs and sections.

## 🎨 Design System Standards

### Layout Structure
All customer pages should follow this consistent structure:

```blade
@extends('layouts.customer-layout')

@section('title', 'Page Title')
@section('page_title', 'Page Title')

@php
    $showNavigation = true;
@endphp

@section('navigation_tabs')
<!-- Consistent navigation tabs -->
@endsection

@section('content')
<!-- Page content -->
@endsection
```

### Navigation Tabs Pattern
**Consistent Navigation Structure:**
```blade
@section('navigation_tabs')
<a href="{{ route('customer.dashboard') }}" class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.dashboard') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="shopping-bag" class="h-4 w-4"></i>
    Services Catalog
</a>
<a href="{{ route('customer.orders') }}" class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.orders') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="package" class="h-4 w-4"></i>
    My Orders
</a>
<a href="{{ route('ai-design.index') }}" class="py-4 px-6 border-b-2 {{ request()->routeIs('ai-design.index') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="sparkles" class="h-4 w-4"></i>
    AI Design
</a>
<a href="{{ route('profile.index') }}" class="py-4 px-6 border-b-2 {{ request()->routeIs('profile.index') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="user" class="h-4 w-4"></i>
    Account Settings
</a>
@endsection
```

### Page Header Pattern
**Consistent Page Headers:**
```blade
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <i data-lucide="icon-name" class="h-8 w-8 text-primary"></i>
        <h1 class="text-3xl font-bold text-gray-900">Page Title</h1>
    </div>
    <p class="text-gray-600 text-lg">Page description</p>
</div>
```

### Card Design Pattern
**Consistent Card Structure:**
```blade
<div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
    <!-- Card Header (Optional) -->
    <div class="h-48 bg-gradient-to-br from-purple-500 to-purple-700 relative">
        <div class="absolute inset-0 bg-black/20"></div>
    </div>
    
    <!-- Card Content -->
    <div class="p-6">
        <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900">Card Title</h3>
            <div class="flex items-center gap-1">
                <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                <span class="text-sm font-medium text-gray-700">Rating</span>
            </div>
        </div>
        
        <p class="text-sm text-gray-600 mb-4">Card Description</p>
        
        <!-- Card Details -->
        <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div class="flex items-center gap-2">
                <i data-lucide="icon" class="h-4 w-4 flex-shrink-0"></i>
                <span>Detail text</span>
            </div>
        </div>
        
        <!-- Card Action -->
        <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
            Action Button
        </button>
    </div>
</div>
```

## 🎯 Color Scheme Standards

### Primary Colors
- **Primary Purple**: `hsl(263, 70%, 50%)` - Used for active states, buttons, icons
- **Primary Hover**: `hsl(263, 70%, 45%)` - Hover states for primary elements
- **Primary Light**: `hsl(263, 70%, 95%)` - Light backgrounds and subtle highlights

### Secondary Colors
- **Gray 900**: `hsl(240, 10%, 3.9%)` - Primary text color
- **Gray 600**: `hsl(220, 9%, 46%)` - Secondary text color
- **Gray 300**: `hsl(240, 5.9%, 90%)` - Border colors
- **Gray 50**: `hsl(240, 4.8%, 97%)` - Background color

### Status Colors
- **Success**: `hsl(142, 76%, 36%)` - Success states, completed orders
- **Warning**: `hsl(38, 92%, 50%)` - Warning states, pending orders
- **Error**: `hsl(0, 84.2%, 60.2%)` - Error states, failed operations

## 📱 Responsive Design Standards

### Breakpoints
- **Mobile**: `< 768px`
- **Tablet**: `768px - 1024px`
- **Desktop**: `> 1024px`

### Grid Patterns
```blade
<!-- Services/Shops Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Cards -->
</div>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- Stat cards -->
</div>

<!-- Form Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form sections -->
</div>
```

## 🔧 Component Standards

### Buttons
```blade
<!-- Primary Button -->
<button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
    Primary Action
</button>

<!-- Secondary Button -->
<button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
    Secondary Action
</button>

<!-- Button with Icon -->
<button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition-colors">
    <i data-lucide="icon-name" class="h-4 w-4"></i>
    Button Text
</button>
```

### Form Elements
```blade
<!-- Input Field -->
<div class="mb-4">
    <label for="field" class="block text-sm font-medium text-gray-700 mb-2">Field Label</label>
    <input type="text" id="field" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
</div>

<!-- Select Field -->
<select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
    <option value="">Select Option</option>
</select>
```

### Status Badges
```blade
<!-- Status Badge -->
<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success">
    <i data-lucide="check" class="h-3 w-3 mr-1"></i>
    Completed
</span>
```

## 📋 Page-Specific Standards

### Services Catalog (Dashboard)
- **Layout**: Grid of shop cards with search and filters
- **Navigation**: Active "Services Catalog" tab
- **Cards**: Purple gradient headers with shop information
- **Actions**: "View Services" buttons

### My Orders
- **Layout**: Statistics cards + order list
- **Navigation**: Active "My Orders" tab
- **Cards**: Order cards with status badges
- **Actions**: "View Details" buttons

### AI Design
- **Layout**: Two-column layout (input + preview)
- **Navigation**: Active "AI Design" tab
- **Cards**: Form cards and result cards
- **Actions**: "Generate Design" buttons

### Account Settings
- **Layout**: Form sections with profile information
- **Navigation**: Active "Account Settings" tab
- **Cards**: Settings cards with form fields
- **Actions**: "Save Changes" buttons

## 🎨 Icon Standards

### Navigation Icons
- **Services Catalog**: `shopping-bag`
- **My Orders**: `package`
- **AI Design**: `sparkles`
- **Account Settings**: `user`

### Action Icons
- **View/Details**: `eye`
- **Edit**: `edit-3`
- **Delete**: `trash-2`
- **Download**: `download`
- **Search**: `search`
- **Filter**: `filter`

### Status Icons
- **Success**: `check-circle`
- **Warning**: `alert-triangle`
- **Error**: `x-circle`
- **Info**: `info`
- **Loading**: `loader-2` (with spin animation)

## 🔄 Animation Standards

### Transitions
```css
/* Standard transition for interactive elements */
.transition-colors {
    transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
}

/* Card hover effects */
.hover:shadow-md {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover:shadow-md:hover {
    transform: translateY(-2px);
}
```

### Loading States
```blade
<!-- Loading Skeleton -->
<div class="animate-pulse">
    <div class="h-32 bg-gray-200 rounded-lg mb-4"></div>
    <div class="h-4 bg-gray-200 rounded mb-2"></div>
    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
</div>
```

## ✅ Consistency Checklist

### For Each Customer Page:
- [ ] Uses `@extends('layouts.customer-layout')`
- [ ] Has consistent navigation tabs with active state
- [ ] Follows standard page header pattern
- [ ] Uses consistent card design
- [ ] Implements proper responsive grid
- [ ] Uses standard color scheme
- [ ] Includes proper Lucide icons
- [ ] Has smooth transitions and hover effects
- [ ] Maintains consistent typography
- [ ] Includes proper loading states

### Navigation Consistency:
- [ ] All tabs use anchor links (not buttons)
- [ ] Active states are properly highlighted
- [ ] Icons are consistent across pages
- [ ] Hover effects work properly
- [ ] Mobile responsiveness is maintained

### Visual Consistency:
- [ ] Purple gradient cards match reference design
- [ ] Shadows and borders are consistent
- [ ] Typography hierarchy is maintained
- [ ] Button styling is uniform
- [ ] Status indicators use standard colors

## 🚀 Implementation Priority

### High Priority (Immediate):
1. **Navigation Tabs**: Ensure all pages use consistent navigation
2. **Page Headers**: Standardize header structure and styling
3. **Card Design**: Implement consistent card patterns
4. **Color Scheme**: Apply uniform color usage

### Medium Priority (Next Sprint):
1. **Form Elements**: Standardize form field styling
2. **Button Variations**: Implement consistent button patterns
3. **Status Indicators**: Standardize status badge design
4. **Loading States**: Implement consistent loading patterns

### Low Priority (Future):
1. **Advanced Animations**: Add micro-interactions
2. **Dark Mode**: Implement dark theme support
3. **Accessibility**: Enhance ARIA labels and keyboard navigation
4. **Performance**: Optimize CSS and JavaScript loading

---

**Last Updated**: November 2024  
**Version**: 1.0  
**Maintainer**: UniPrint Development Team
