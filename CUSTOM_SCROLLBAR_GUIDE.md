# Custom Scrollbar Implementation Guide

## Overview
Custom scrollbars have been implemented for the BRI Corporate inventory system using CSS custom properties and Tailwind utilities.

## Features
- ✅ **Corporate Theme Matching**: Uses BRI blue gradient colors
- ✅ **Cross-browser Support**: Webkit (Chrome/Safari/Edge) + Firefox
- ✅ **Multiple Variants**: Different sizes for different components
- ✅ **Hover Effects**: Interactive feedback with smooth transitions
- ✅ **Responsive Design**: Adapts to component context

## Available Scrollbar Styles

### 1. Default Corporate Scrollbar
**Automatic**: Applied to all scrollable elements
- Width: 12px
- Colors: Corporate blue gradient
- Hover: Enhanced gradient with scale effect

### 2. Thin Scrollbar (Utility Class)
**Usage**: Add `scrollbar-thin` class
```html
<div class="scrollbar-thin overflow-y-auto max-h-96">
  <!-- Content -->
</div>
```
- Width: 6px
- Perfect for: Dropdowns, small containers

### 3. Enhanced Corporate Scrollbar (Utility Class)
**Usage**: Add `scrollbar-corporate` class
```html
<div class="scrollbar-corporate overflow-auto max-h-screen">
  <!-- Content -->
</div>
```
- Width: 14px
- Enhanced shadows and borders
- Perfect for: Main content areas, tables

### 4. Hidden Scrollbar (Utility Class)
**Usage**: Add `scrollbar-hide` class
```html
<div class="scrollbar-hide overflow-x-auto">
  <!-- Content still scrollable, but scrollbar hidden -->
</div>
```

## Component-Specific Scrollbars

### Sidebar
- Thin white/transparent scrollbar
- Blends with sidebar gradient background

### Tables (Filament)
- Orange accent color matching action buttons
- Medium size (10px)

### Modals & Dropdowns
- Thin scrollbar (8px)
- Semi-transparent styling

## CSS Variables Used
```css
--corporate-blue: #00529B
--bright-blue: #0073E6
--neutral-background: #F2F4F7
--action-orange: #F37021
--white-semi: rgba(255, 255, 255, 0.8)
```

## Browser Support
- ✅ **Chrome/Chromium**: Full custom styling
- ✅ **Safari**: Full custom styling  
- ✅ **Edge**: Full custom styling
- ✅ **Firefox**: Limited styling (thin scrollbar with colors)
- ⚠️ **Internet Explorer**: Basic fallback

## Implementation Examples

### For Filament Resources
```php
// In your Blade view or component
<div class="scrollbar-corporate">
    {{ $this->table }}
</div>
```

### For Custom Components
```html
<!-- Large content area -->
<div class="scrollbar-corporate overflow-auto max-h-96 p-4">
    <!-- Your content -->
</div>

<!-- Compact sidebar -->
<aside class="scrollbar-thin overflow-y-auto h-screen">
    <!-- Navigation items -->
</aside>

<!-- Hidden scrollbar for horizontal scroll -->
<div class="scrollbar-hide overflow-x-auto whitespace-nowrap">
    <!-- Horizontal content -->
</div>
```

## Performance Notes
- Uses CSS-only implementation (no JavaScript)
- Minimal performance impact
- Graceful degradation on unsupported browsers
- Leverages hardware acceleration for smooth animations

## Customization
To modify scrollbar appearance, edit the CSS custom properties in:
`resources/css/filament/admin/theme.css`

The scrollbar automatically inherits your theme colors and will update when you change the CSS variables.
