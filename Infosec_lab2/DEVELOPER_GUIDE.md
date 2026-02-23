# UI Developer Reference Guide

## Quick Start

### Using Built-in Components

#### Buttons
```html
<!-- Primary Button -->
<button class="btn">Click Me</button>

<!-- Button Sizes -->
<button class="btn btn-sm">Small</button>
<button class="btn">Default</button>
<button class="btn btn-lg">Large</button>

<!-- Button Colors -->
<button class="btn">Primary</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Delete</button>
<button class="btn btn-warning">Warning</button>
<button class="btn btn-outline">Outline</button>
```

#### Cards
```html
<div class="card">
    <div class="card-header">
        <h3>Card Title</h3>
    </div>
    <div class="card-body">
        Content goes here
    </div>
    <div class="card-footer">
        Footer content
    </div>
</div>
```

#### Forms
```html
<div class="form-group">
    <label for="name">Your Name <span class="required">*</span></label>
    <input type="text" id="name" placeholder="Enter name" required>
</div>
```

#### Alerts
```html
<!-- Success Alert -->
<div class="alert alert-success">✓ Success message</div>

<!-- Error Alert -->
<div class="alert alert-error">✗ Error message</div>

<!-- Warning Alert -->
<div class="alert alert-warning">⚠ Warning message</div>

<!-- Info Alert -->
<div class="alert alert-info">ℹ Info message</div>
```

#### Tables
```html
<table>
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
        </tr>
    </tbody>
</table>
```

---

## CSS Variables (Theming)

All colors and measurements are defined as CSS variables at the `:root` level:

```css
:root {
    --primary-color: #6366f1;
    --primary-dark: #4f46e5;
    --secondary-color: #ec4899;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    --dark-bg: #0f172a;
    --light-bg: #f8fafc;
    --card-bg: #ffffff;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.15);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Changing Theme

To change colors globally, modify the `:root` variables:

```css
:root {
    --primary-color: #your-color;
    --success-color: #your-color;
    /* etc */
}
```

---

## Utility Classes

### Spacing
```html
<!-- Margin Top -->
<div class="mt-1">Small margin top</div>
<div class="mt-2">Medium margin top</div>
<div class="mt-3">Large margin top</div>
<div class="mt-4">Extra large margin top</div>

<!-- Margin Bottom -->
<div class="mb-1">Small margin bottom</div>
<div class="mb-2">Medium margin bottom</div>

<!-- Padding -->
<div class="p-2">Small padding</div>
<div class="p-3">Medium padding</div>
<div class="p-4">Large padding</div>

<!-- Gap (for flex) -->
<div class="flex gap-1">Items with small gap</div>
<div class="flex gap-2">Items with medium gap</div>
<div class="flex gap-3">Items with large gap</div>
```

### Flex Layout
```html
<div class="flex">Flex container</div>
<div class="flex-between">Flex with space-between</div>
```

### Text Alignment
```html
<div class="text-center">Centered text</div>
<div class="text-right">Right-aligned text</div>
```

---

## Responsive Design

### Media Queries
```css
/* Desktop (default) */
.element { /* styles */ }

/* Tablet */
@media (max-width: 768px) {
    .element { /* tablet styles */ }
}

/* Mobile */
@media (max-width: 480px) {
    .element { /* mobile styles */ }
}
```

### Common Patterns
```html
<!-- Responsive Grid -->
<div class="grid grid-2">
    <div class="card">Column 1</div>
    <div class="card">Column 2</div>
</div>

<!-- Responsive Button Group -->
<div class="flex" style="flex-wrap: wrap; gap: 12px;">
    <button class="btn">Button 1</button>
    <button class="btn">Button 2</button>
</div>
```

---

## Creating New Pages

### Basic Template
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <!-- Your content here -->
</div>

</body>
</html>
```

### Form Template
```html
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2>Form Title</h2>
    </div>
    <div class="card-body">
        <form>
            <div class="form-group">
                <label for="field">Field Label</label>
                <input type="text" id="field" placeholder="Enter value" required>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>
</div>
```

---

## Common Patterns

### Success/Error Message
```html
<!-- Success -->
<div class="alert alert-success">✓ Operation completed successfully</div>

<!-- Error -->
<div class="alert alert-error">✗ An error occurred. Please try again.</div>
```

### Loading State
```html
<button class="btn loading">
    <span class="spinner"></span> Loading...
</button>
```

### Empty State
```html
<div class="empty-state">
    <div class="empty-state-icon">📦</div>
    <div class="empty-state-title">No items found</div>
    <div class="empty-state-text">Start by creating your first item</div>
</div>
```

### Disabled State
```html
<button class="btn" disabled>Disabled Button</button>
```

---

## Best Practices

1. **Use CSS Variables**: Reference colors via CSS variables, not hardcoded values
2. **Consistent Spacing**: Use the spacing scale (0.5rem, 1rem, 1.5rem, 2rem)
3. **Mobile First**: Design for mobile, then enhance for desktop
4. **Accessibility**: Always use proper labels and semantic HTML
5. **Transitions**: Use `var(--transition)` for consistency
6. **Card Containers**: Wrap content in `.card` for consistency
7. **Button Groups**: Use `.flex` with `gap-2` for button groups

---

## Debugging Tips

### Check Browser DevTools
- Look at computed CSS to verify variables
- Check responsive breakpoints
- Use mobile device emulation in DevTools

### Common Issues
1. **Button not styled**: Ensure it has `.btn` class
2. **Form not responsive**: Wrap in `.container`
3. **Colors not applying**: Check CSS variable spelling
4. **Mobile layout broken**: Check media queries in style.css

---

## Browser Compatibility

✅ Chrome/Edge (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Mobile browsers (iOS Safari, Chrome Mobile)

❌ Internet Explorer (not supported)

---

## Performance Tips

1. Use CSS variables for theming (no recompilation needed)
2. Leverage CSS Grid/Flexbox (native browser support)
3. Minimize custom CSS (use utility classes)
4. Cache static assets
5. Optimize images

---

## Customization Guide

### Change Primary Color
In `style.css`, find `:root` and modify:
```css
--primary-color: #your-hex-color;
--primary-dark: #your-darker-hex-color;
```

### Add Custom Font
Add to `<head>`:
```html
<link href="https://fonts.googleapis.com/css2?family=Your+Font" rel="stylesheet">
```

Then update in `style.css`:
```css
font-family: 'Your Font', sans-serif;
```

### Adjust Spacing
Modify margin/padding values in utility classes:
```css
.mt-1 { margin-top: 0.75rem; } /* change from 0.5rem */
```

---

## Need Help?

Refer to the `UI_IMPROVEMENTS.md` file for detailed documentation on all improvements made to the system.
