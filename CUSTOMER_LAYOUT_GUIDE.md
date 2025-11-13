# Customer Layout Standardization Guide

## Overview

This guide documents the standardized customer layout system implemented for UniPrint's customer-facing pages. The new layout ensures visual consistency, improved user experience, and easier maintenance across all customer-related pages.

## Layout Structure

### Main Layout File
- **File**: `resources/views/layouts/customer-layout.blade.php`
- **Purpose**: Standardized layout template for all customer pages
- **Extends**: None (base layout)

### Key Features

#### 1. Consistent Header
- UniPrint logo and branding
- Dynamic page title
- User profile information
- Notification and saved services indicators
- Responsive design for mobile devices

#### 2. Optional Components
- **Promotional Banner**: Configurable promotional content
- **Navigation Tabs**: Tab-based navigation for complex pages
- **Footer**: Standard footer with links and contact information

#### 3. Standardized Styling
- **CSS Classes**: Predefined classes for consistent styling
- **Color Scheme**: Unified color palette using CSS custom properties
- **Typography**: Consistent font sizes and weights
- **Spacing**: Standardized padding and margins

## Usage Instructions

### Basic Page Implementation

```blade
@extends('layouts.customer-layout')

@section('title', 'Page Title')
@section('page_title', 'Display Title')

@section('content')
<!-- Your page content here -->
@endsection
```

### Advanced Features

#### Promotional Banner
```blade
@php
    $showPromoBanner = true;
@endphp

@section('promo_title', 'Special Offer!')
@section('promo_description', 'Get 20% off on business card printing')
@section('promo_cta', 'Shop Now')
```

#### Navigation Tabs
```blade
@php
    $showNavigation = true;
@endphp

@section('navigation_tabs')
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-primary text-primary font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="tab1" role="tab" aria-selected="true">
    <i data-lucide="icon-name" class="h-4 w-4"></i>
    Tab Label
</button>
@endsection
```

#### Hide Footer
```blade
@php
    $hideFooter = true;
@endphp
```

## Standardized CSS Classes

### Form Controls
- `customer-input`: Standard input field styling
- `customer-button-primary`: Primary action button
- `customer-button-secondary`: Secondary action button

### Cards and Components
- `customer-card`: Standard card with hover effects
- `status-badge`: Status indicator badge
- `status-pending`, `status-in-progress`, `status-completed`: Status-specific styling

### Layout Utilities
- `skeleton`: Loading skeleton animation
- `error-state`: Error message container
- `success-notification`: Success notification styling
- `error-notification`: Error notification styling

## JavaScript Utilities

### Global Functions
The layout provides `window.CustomerLayout` object with utility functions:

```javascript
// Show/hide loading overlay
CustomerLayout.showLoading();
CustomerLayout.hideLoading();

// Show notifications
CustomerLayout.showNotification('Success message', 'success');
CustomerLayout.showNotification('Error message', 'error');

// Update saved services badge
CustomerLayout.updateSavedServicesBadge(count);

// Standard AJAX requests with CSRF
CustomerLayout.request('/api/endpoint', {
    method: 'POST',
    body: JSON.stringify(data)
});
```

## Responsive Design

### Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Mobile Optimizations
- Collapsible navigation
- Simplified header layout
- Touch-friendly button sizes
- Optimized typography

## Page Examples

### 1. Dashboard Page
- **File**: `customer/dashboard-refactored.blade.php`
- **Features**: Tab navigation, promotional banner, service grid
- **Layout**: Full layout with all components

### 2. Orders Page
- **File**: `customer/orders-refactored.blade.php`
- **Features**: Statistics cards, order list, pagination
- **Layout**: Standard layout without tabs

### 3. Saved Services Page
- **File**: `customer/saved-services-refactored.blade.php`
- **Features**: Service grid, bulk actions, empty states
- **Layout**: Standard layout with action buttons

## Migration Guide

### Converting Existing Pages

1. **Update Layout Extension**
   ```blade
   // Old
   @extends('layouts.app')
   
   // New
   @extends('layouts.customer-layout')
   ```

2. **Update Section Names**
   ```blade
   @section('title', 'Page Title')
   @section('page_title', 'Display Title')
   @section('content')
   ```

3. **Replace Custom Styling**
   - Use standardized CSS classes
   - Remove custom header/footer code
   - Update form controls to use customer-* classes

4. **Update JavaScript**
   - Use CustomerLayout utility functions
   - Remove duplicate notification code
   - Standardize AJAX requests

### Form Controls Migration
```blade
<!-- Old -->
<input type="text" class="form-control">
<button class="btn btn-primary">Submit</button>

<!-- New -->
<input type="text" class="customer-input">
<button class="customer-button-primary">Submit</button>
```

### Status Badges Migration
```blade
<!-- Old -->
<span class="badge badge-warning">Pending</span>

<!-- New -->
<span class="status-badge status-pending">Pending</span>
```

## Best Practices

### 1. Consistent Styling
- Always use predefined CSS classes
- Follow the established color scheme
- Maintain consistent spacing

### 2. Responsive Design
- Test on multiple screen sizes
- Use responsive grid classes
- Optimize for touch interactions

### 3. Accessibility
- Include proper ARIA labels
- Maintain keyboard navigation
- Use semantic HTML elements

### 4. Performance
- Minimize custom CSS
- Use efficient JavaScript
- Optimize images and assets

### 5. Error Handling
- Implement proper error states
- Show loading indicators
- Provide user feedback

## Component Library

### Cards
```blade
<div class="customer-card bg-white rounded-lg shadow-sm p-6">
    <!-- Card content -->
</div>
```

### Forms
```blade
<form class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
        <input type="text" class="customer-input">
    </div>
    <button type="submit" class="customer-button-primary">Submit</button>
</form>
```

### Status Indicators
```blade
<span class="status-badge status-pending">Pending</span>
<span class="status-badge status-in-progress">In Progress</span>
<span class="status-badge status-completed">Completed</span>
```

### Empty States
```blade
<div class="bg-white rounded-lg shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="icon-name" class="h-8 w-8 text-gray-400"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">Title</h3>
    <p class="text-gray-600 mb-6">Description</p>
    <button class="customer-button-primary">Action</button>
</div>
```

## Maintenance

### Adding New Components
1. Define CSS classes in the layout file
2. Document usage in this guide
3. Create examples for reference
4. Test across different screen sizes

### Updating Styles
1. Modify CSS custom properties for global changes
2. Update component classes for specific changes
3. Test all affected pages
4. Update documentation

### Performance Monitoring
- Monitor page load times
- Check for CSS/JS conflicts
- Optimize asset loading
- Review responsive performance

## Support

For questions or issues with the customer layout system:
1. Check this documentation first
2. Review existing page examples
3. Test changes in development environment
4. Document any new patterns or components
