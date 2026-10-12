# 🏗️ Application Foundation

This document describes the foundation layer of the Social Media application built with Laravel, PHP, Blade templates, MySQL, and Tailwind CSS.

## 📋 Overview

The foundation layer provides the structural backbone for the application, including:

- **Configuration**: Application and environment settings
- **Layout System**: Reusable Blade templates and components
- **Design System**: Consistent UI elements and styling
- **Error Handling**: User-friendly error pages
- **Frontend Build**: Vite + Tailwind CSS setup

## 🎯 Features Implemented

### ✅ Configuration
- [x] Application configuration (`config/app.php`)
- [x] View configuration (`config/view.php`)
- [x] Filesystem configuration (`config/filesystems.php`)
- [x] Environment variables (`.env`, `.env.example`)
- [x] Tailwind CSS v4 configuration
- [x] Vite build configuration

### ✅ Layout System
- [x] Main layout (`resources/views/layouts/app.blade.php`)
- [x] Header partial (`resources/views/layouts/partials/header.blade.php`)
- [x] Footer partial (`resources/views/layouts/partials/footer.blade.php`)
- [x] Responsive navigation
- [x] Mobile menu support
- [x] Theme toggle (light/dark mode)

### ✅ Component Library

#### Alerts
- [x] Flash message component (`components/alerts/flash.blade.php`)
- [x] Auto-dismiss after 5 seconds
- [x] Multiple types: success, error, warning, info

#### Buttons
- [x] Primary button (`components/buttons/primary.blade.php`)
- [x] Secondary button (`components/buttons/secondary.blade.php`)
- [x] Danger button (`components/buttons/danger.blade.php`)
- [x] Icon button (`components/buttons/icon.blade.php`)
- [x] Multiple sizes: sm, md, lg
- [x] Loading state support
- [x] Full-width option

#### Cards
- [x] Base card (`components/cards/base.blade.php`)
- [x] Stat card (`components/cards/stat.blade.php`)
- [x] Multiple color variants

#### Forms
- [x] Input field (`components/forms/input.blade.php`)
- [x] Textarea (`components/forms/textarea.blade.php`)
- [x] Select dropdown (`components/forms/select.blade.php`)
- [x] Checkbox (`components/forms/checkbox.blade.php`)
- [x] File upload (`components/forms/file.blade.php`)
- [x] Error state handling
- [x] Hint text support
- [x] Required field indicators

#### Icons
- [x] User icon (`components/icons/user.blade.php`)
- [x] Placeholder icon (`components/icons/placeholder.blade.php`)
- [x] Multiple sizes: xs, sm, md, lg, xl, 2xl

### ✅ Error Pages
- [x] 404 Not Found (`errors/404.blade.php`)
- [x] 403 Forbidden (`errors/403.blade.php`)
- [x] 419 Page Expired (`errors/419.blade.php`)
- [x] 429 Too Many Requests (`errors/429.blade.php`)
- [x] 500 Server Error (`errors/500.blade.php`)
- [x] Consistent styling across all error pages
- [x] Helpful actions (Go Home, Go Back, Try Again)

### ✅ Pages
- [x] Home page (`pages/home.blade.php`)
- [x] Welcome section
- [x] Feature cards
- [x] Platform stats
- [x] Trending topics
- [x] Call-to-action section

### ✅ Design System
- [x] Color palette (primary, secondary, success, warning, danger, info)
- [x] Typography scale
- [x] Spacing system
- [x] Border radius variants
- [x] Shadow levels
- [x] Transition utilities
- [x] Custom scrollbar for dark mode
- [x] Focus states
- [x] Responsive breakpoints

### ✅ Frontend Assets
- [x] Tailwind CSS v4 integration
- [x] Alpine.js integration
- [x] Vite build system
- [x] Global utility functions (`resources/js/app.js`)
- [x] Theme management (light/dark mode)
- [x] Loading indicators
- [x] Form submission handling
- [x] Number formatting
- [x] Date formatting
- [x] Clipboard utilities

### ✅ Routes
- [x] Home route (`/`)
- [x] Named route: `home`
- [x] Search route (`/search`)
- [x] Health check endpoint (`/health`)
- [x] Fallback route for 404 errors

### ✅ Service Provider
- [x] View composers for shared data
- [x] Blade directives for common operations
- [x] Date/time formatting
- [x] Number formatting
- [x] String utilities

## 📁 File Structure

```
Social-media-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Controller.php
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   └── view.php
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── components/
│       │   ├── alerts/
│       │   │   └── flash.blade.php
│       │   ├── buttons/
│       │   │   ├── danger.blade.php
│       │   │   ├── icon.blade.php
│       │   │   ├── primary.blade.php
│       │   │   └── secondary.blade.php
│       │   ├── cards/
│       │   │   ├── base.blade.php
│       │   │   └── stat.blade.php
│       │   ├── forms/
│       │   │   ├── checkbox.blade.php
│       │   │   ├── file.blade.php
│       │   │   ├── input.blade.php
│       │   │   ├── select.blade.php
│       │   │   └── textarea.blade.php
│       │   └── icons/
│       │       ├── placeholder.blade.php
│       │       └── user.blade.php
│       ├── errors/
│       │   ├── 403.blade.php
│       │   ├── 404.blade.php
│       │   ├── 419.blade.php
│       │   ├── 429.blade.php
│       │   └── 500.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── partials/
│       │       ├── footer.blade.php
│       │       └── header.blade.php
│       └── pages/
│           └── home.blade.php
├── routes/
│   └── web.php
├── public/
│   ├── css/
│   ├── images/
│   │   └── .gitkeep
│   └── js/
├── storage/
│   └── app/
│       └── public/
│           └── .gitkeep
├── .editorconfig
├── .env
├── .env.example
├── config-check.php
├── FOUNDATION.md
├── composer.json
├── package.json
├── phpunit.xml
└── vite.config.js
```

## 🎨 Design System

### Colors

| Name | Light Mode | Dark Mode | Usage |
|------|------------|-----------|-------|
| Primary | `#3b82f6` | `#60a5fa` | Buttons, links, accents |
| Success | `#10b981` | `#34d399` | Success states |
| Warning | `#f59e0b` | `#fbbf24` | Warnings |
| Danger | `#ef4444` | `#f87171` | Errors, destructive actions |
| Info | `#06b6d4` | `#22d3ee` | Information |

### Typography

- **Font Family**: Inter (system fallback)
- **Base Size**: 16px
- **Scale**: 1rem = 16px

### Spacing

| Name | Value | Usage |
|------|-------|-------|
| xs | 0.25rem (4px) | Tight spacing |
| sm | 0.5rem (8px) | Small spacing |
| md | 1rem (16px) | Default spacing |
| lg | 1.5rem (24px) | Large spacing |
| xl | 2rem (32px) | Extra large spacing |
| 2xl | 3rem (48px) | Section spacing |

### Border Radius

| Name | Value | Usage |
|------|-------|-------|
| sm | 0.25rem (4px) | Subtle rounding |
| md | 0.375rem (6px) | Default rounding |
| lg | 0.5rem (8px) | Cards, inputs |
| xl | 0.75rem (12px) | Large cards |
| 2xl | 1rem (16px) | Modal, dialogs |
| full | 9999px | Pills, avatars |

### Shadows

| Name | Value | Usage |
|------|-------|-------|
| sm | `0 1px 2px 0 rgb(0 0 0 / 0.05)` | Subtle shadows |
| default | `0 1px 3px 0 rgb(0 0 0 / 0.1)` | Default shadows |
| md | `0 4px 6px -1px rgb(0 0 0 / 0.1)` | Cards |
| lg | `0 10px 15px -3px rgb(0 0 0 / 0.1)` | Modals |
| xl | `0 20px 25px -5px rgb(0 0 0 / 0.1)` | Large elements |

## 🚀 Usage

### Running the Application

#### Development
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations (when ready)
php artisan migrate

# Start development servers
npm run dev
php artisan serve
```

#### Production Build
```bash
npm run build
```

### Using Components

#### Buttons
```blade
<x-buttons.primary size="md" full>
    Submit
</x-buttons.primary>

<x-buttons.secondary loading>
    Processing...
</x-buttons.secondary>

<x-buttons.danger type="submit">
    Delete
</x-buttons.danger>

<x-buttons.icon size="lg" variant="primary">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
</x-buttons.icon>
```

#### Forms
```blade
<x-forms.input 
    name="username" 
    label="Username" 
    placeholder="Enter your username" 
    required
    error="{{ $errors->first('username') }}"
/>

<x-forms.textarea 
    name="bio" 
    label="Bio" 
    placeholder="Tell us about yourself" 
    rows="5"
    hint="Maximum 500 characters"
/>

<x-forms.select 
    name="country" 
    label="Country" 
    :options="$countries"
    placeholder="Select your country"
/>

<x-forms.checkbox 
    name="terms" 
    label="I agree to the terms and conditions" 
    required
/>

<x-forms.file 
    name="avatar" 
    label="Profile Picture" 
    accept="image/*"
/>
```

#### Cards
```blade
<x-cards.base title="User Profile" subtitle="Manage your profile settings">
    <!-- Card content -->
    <p>Your profile information goes here.</p>
</x-cards.base>

<x-cards.stat 
    label="Followers" 
    value="1,234" 
    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>'
    color="blue"
/>
```

#### Alerts
```blade
<x-alerts.flash type="success" message="Your changes have been saved!" />

-- In controller --
return redirect()->back()->with('success', 'Your changes have been saved!');
```

### JavaScript Utilities

The application includes global utilities available via `window.Social`:

```javascript
// Format numbers
Social.formatNumber(1500); // "1.5K"

// Format dates
Social.formatDate('2024-01-15T10:30:00Z'); // "2 weeks ago"

// Copy to clipboard
Social.copyToClipboard('text to copy');

// Show/hide loading indicator
Social.showLoading();
Social.hideLoading();

// Debounce function
const debouncedSearch = Social.debounce(searchFunction, 300);

// Throttle function
const throttledScroll = Social.throttle(scrollHandler, 100);
```

## 🧪 Testing

### Configuration Checks

Run the configuration check script to verify the setup:
```bash
php config-check.php
```

This will verify:
- PHP version
- Composer configuration
- Environment variables
- Directory structure
- Key files
- Routes
- View files
- Configuration files

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=Feature
```

## 📝 Customization

### Adding New Components

1. Create a new file in the appropriate `resources/views/components/` directory
2. Use the `@props` directive to define component properties
3. Follow the existing naming convention (kebab-case)
4. Add proper documentation in the component file

Example:
```blade
@props(['size' => 'md', 'color' => 'primary'])

<div class="badge badge-{{ $color }} badge-{{ $size }}">
    {{ $slot }}
</div>
```

### Adding New Pages

1. Create a new file in `resources/views/pages/`
2. Extend the main layout: `@extends('layouts.app')`
3. Define sections: `@section('title')`, `@section('content')`
4. Add the route in `routes/web.php`

### Modifying Theme

Edit the Tailwind theme in `resources/css/app.css`:
```css
@theme {
    --font-sans: 'Your Font', sans-serif;
    --color-primary: #your-color;
    /* ... */
}
```

## 🎯 Next Steps

With the foundation in place, you can now implement:

1. **Authentication System**
   - Login/Registration controllers
   - Auth views
   - Password reset
   - Email verification

2. **User Management**
   - User profiles
   - Profile editing
   - Avatar uploads
   - Account settings

3. **Social Features**
   - Post creation
   - News feed
   - Comments
   - Likes/Reactions
   - Following system
   - Notifications

4. **Advanced Features**
   - Image uploads
   - Search functionality
   - Direct messages
   - Activity feed
   - Real-time updates

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Vite Documentation](https://vitejs.dev)

## 🔧 Troubleshooting

### Common Issues

**Tailwind classes not working:**
- Ensure Vite is running: `npm run dev`
- Check that `@vite` directive is used in Blade templates
- Verify Tailwind is imported in `resources/css/app.css`

**Alpine.js not working:**
- Ensure Alpine is imported in `resources/js/app.js`
- Check that `Alpine.start()` is called
- Verify Vite is processing the JavaScript

**Dark mode not working:**
- Check that `dark:` variants are used
- Verify theme toggle script is loaded
- Ensure localStorage is not blocked

**Images not loading:**
- Check filesystem configuration
- Verify symbolic link: `php artisan storage:link`
- Ensure proper permissions on storage directory

## 📄 License

This foundation is open-source and follows Laravel's MIT license.
