# Wink Volt Generator

🚀 **The ultimate Laravel package for rapid TALL stack development**

Generate complete, production-ready Laravel Volt components from your Eloquent models with a single command. Create full CRUD interfaces with smart forms, advanced data tables, responsive modals, and modern UI components—all optimized for Laravel 12 and Livewire 3.

## Features

✨ **6 Powerful Generators:**
- 📊 **DataTables** - Advanced tables with search, sorting, pagination, and CRUD actions
- 📈 **Charts** - Interactive Chart.js visualizations with multiple chart types
- 📝 **Forms** - Smart forms with automatic field type detection and validation
- 🪟 **Modals** - Accessible CRUD modals with Alpine.js integration
- 🎴 **Cards** - Responsive card layouts with pagination and search
- 🔍 **Search** - Real-time search with debouncing and filters

🎯 **What's New:**
- ✅ **Smart Field Detection** - Boolean fields auto-generate as checkboxes, decimals include step attributes
- ✅ **Enhanced DataTables** - Full-featured tables with live search, sorting, pagination, and action buttons
- ✅ **Professional Modals** - Properly structured CRUD modals with validation and state management
- ✅ **Modern UI** - Tailwind CSS 4.x compatible with hover states and smooth transitions
- ✅ **Better Performance** - Optimized queries, computed properties, and proper variable scoping

## Installation

```bash
composer require wink/volt-generator
```

### Frontend Setup

For charts, install Chart.js:
```bash
npm install chart.js
```

Add to your `resources/js/app.js`:
```javascript
import Chart from 'chart.js/auto';
window.Chart = Chart;
```

## Quick Start

### Complete CRUD in 30 seconds

```bash
# Generate a complete product management suite
php artisan make:volt-datatable Product
php artisan make:volt-form Product --action=both  
php artisan make:volt-modal Product --type=crud
php artisan make:volt-card Product --layout=grid
php artisan make:volt-search Product
php artisan make:volt-chart Product
```

This generates:
- ✅ **DataTable** with search, sorting, pagination, and CRUD actions
- ✅ **Form** with smart field detection and validation
- ✅ **Modal** for inline editing and creation
- ✅ **Cards** for grid/list views with pagination
- ✅ **Search** with real-time filtering
- ✅ **Chart** for data visualization

### Using Your Components

```blade
{{-- In your Blade view --}}
<div class="space-y-6">
    {{-- Search and create bar --}}
    <div class="flex justify-between items-center">
        <livewire:products.search />
        <button onclick="$dispatch('open-modal')" class="btn-primary">
            Add Product
        </button>
    </div>
    
    {{-- Main data table --}}
    <livewire:products.data-table />
    
    {{-- Creation/edit modal --}}
    <livewire:products.crud-modal />
</div>
```

## Component Generators

### 📊 DataTable Generator

Generate feature-rich data tables with modern functionality:

```bash
# Advanced data table with full CRUD
php artisan make:volt-datatable User

# Result: app/Livewire/Users/DataTable.php
```

**✨ Enhanced Features:**
- 🔍 **Live Search** - Real-time search with 300ms debouncing across text fields
- 🔄 **Sortable Columns** - Click headers to sort by any field (asc/desc)
- 📄 **Smart Pagination** - Configurable per-page options (10, 25, 50, 100)
- ⚡ **CRUD Actions** - View, Edit, Delete buttons with confirmation dialogs
- 📱 **Responsive Design** - Mobile-optimized table with proper overflow handling
- 💫 **Empty States** - Beautiful no-data states with helpful messaging
- 🎨 **Modern UI** - Hover effects, loading states, and success notifications

### 📝 Form Generator

Create intelligent CRUD forms with automatic field detection:

```bash
# Create form
php artisan make:volt-form User --action=create

# Edit form  
php artisan make:volt-form User --action=edit

# Combined create/edit form (recommended)
php artisan make:volt-form User --action=both
```

**🧠 Smart Features:**
- 🎯 **Intelligent Field Types** - Auto-detects field types from database schema:
  - Boolean fields (`is_active`, `enabled`) → Checkboxes
  - Text/longtext → Textareas with proper sizing
  - Decimal/float → Number inputs with `step="0.01"` for price fields
  - Email fields → Email inputs with validation
  - Timestamps → DateTime-local inputs
- ✅ **Dynamic Validation** - Generates appropriate rules based on column types
- 🔄 **Loading States** - Smooth loading indicators with disabled states
- 🚨 **Error Handling** - Field-level error display with proper styling
- 🛡️ **Security** - CSRF protection and proper sanitization

### 🪟 Modal Generator

Build professional, accessible modals with Alpine.js integration:

```bash
# CRUD operations modal (recommended)
php artisan make:volt-modal User --type=crud

# Confirmation dialog
php artisan make:volt-modal User --type=confirm

# Read-only view modal
php artisan make:volt-modal User --type=view

# Custom modal template
php artisan make:volt-modal User --type=custom
```

**🎭 Professional Features:**
- 🎨 **Modern Design** - Clean modal layouts with proper spacing and typography
- ♿ **Full Accessibility** - ARIA labels, keyboard navigation (ESC to close), focus management
- 🏔️ **Alpine.js Integration** - Smooth transitions, backdrop clicks, escape key handling
- 📱 **Responsive Sizing** - Adapts to screen size with proper mobile breakpoints
- 🔄 **Smart States** - Loading overlays, form validation, success/error messaging
- 🎯 **Dynamic Titles** - Context-aware titles ("Edit User" vs "Create User")
- 🛡️ **Safe Operations** - Confirmation dialogs for destructive actions

### 🎴 Card Generator

Create beautiful card layouts:

```bash
# Grid layout (default)
php artisan make:volt-card Product --layout=grid --columns=3

# List layout
php artisan make:volt-card Product --layout=list

# Masonry layout
php artisan make:volt-card Product --layout=masonry --columns=4
```

**Features:** Multiple layouts, responsive grids, hover effects, pagination, search integration, loading skeletons.

### 🔍 Search Generator

Advanced search and filtering:

```bash
# Auto-detect searchable fields
php artisan make:volt-search Product

# Specify custom fields
php artisan make:volt-search Product --fields=name,description --filters=category,status
```

**Features:** Real-time search, filter chips, date ranges, dropdowns, debouncing, result counts.

### 📈 Chart Generator

Interactive data visualizations:

```bash
# Basic bar chart
php artisan make:volt-chart Order

# Advanced options
php artisan make:volt-chart Order --type=line --metric=sum --metric-column=total_price --time-unit=month
```

**Options:**
- `--type`: Chart type (bar, line, pie, doughnut) - default: bar
- `--dimension`: Column for labels - default: created_at  
- `--metric`: Aggregation (count, sum, avg) - default: count
- `--metric-column`: Column for sum/avg calculations
- `--time-unit`: Time grouping (day, month, year) - default: day

## Configuration

### Publish Configuration

```bash
php artisan vendor:publish --tag="wink-volt-generator-config"
```

### Publish Stubs for Customization

```bash
php artisan vendor:publish --tag="wink-volt-generator-stubs"
```

### Configuration Options

```php
return [
    'path' => 'app/Livewire',
    
    'datatable' => [
        'exclude_columns' => ['id', 'password', 'remember_token', 'created_at', 'updated_at'],
    ],
    
    'form' => [
        'default_action' => 'create',
        'field_types' => ['email' => 'email', 'phone' => 'tel'],
    ],
    
    'modal' => [
        'default_type' => 'crud',
        'sizes' => ['confirm' => 'sm', 'view' => 'lg'],
    ],
    
    'card' => [
        'default_layout' => 'grid',
        'default_columns' => 3,
        'per_page' => 12,
    ],
    
    'search' => [
        'debounce_delay' => 300,
        'per_page' => 10,
    ],
];
```

## Usage Examples

### Complete User Management

```blade
{{-- List users in a card grid --}}
<livewire:users.card />

{{-- Search and filter users --}}
<livewire:users.search />

{{-- User creation form --}}
<livewire:users.form />

{{-- User management modal --}}
<livewire:users.modal />

{{-- Users data table --}}
<livewire:users.data-table />

{{-- User statistics chart --}}
<livewire:users.chart />
```

### Integration Example

```blade
<div class="space-y-6">
    {{-- Search bar --}}
    <livewire:products.search />
    
    {{-- Product grid --}}
    <livewire:products.card layout="grid" columns="4" />
    
    {{-- Product creation modal --}}
    <livewire:products.modal type="crud" />
</div>
```

## Generated Component Features

### ✅ Production Ready
- **Responsive Design:** Mobile-first Tailwind CSS
- **Accessibility:** ARIA labels, keyboard navigation, screen readers
- **Performance:** Lazy loading, pagination, debounced search
- **UX:** Loading states, transitions, error handling

### ✅ Developer Friendly
- **Livewire Integration:** Full state management and reactivity
- **Alpine.js Enhanced:** Interactive behaviors and animations  
- **Validation:** Built-in form validation with error display
- **Customizable:** Override any template via published stubs

### ✅ Framework Integration
- **Laravel Conventions:** Follows Laravel naming and structure
- **Volt Functional API:** Uses modern Volt syntax with `state()` and `mount()`
- **Eloquent Integration:** Automatic model introspection and relationships
- **Route Model Binding:** Seamless integration with Laravel routing

## Technical Excellence

### 🔧 Recent Improvements

**Fixed Critical Issues:**
- ✅ **Template Placeholders** - Fixed `{model_class}` replacement in mount methods
- ✅ **Blade Syntax** - Corrected malformed PHP in modal validation blocks
- ✅ **Variable Scoping** - Resolved DataTable variable naming conflicts
- ✅ **Field Detection** - Enhanced boolean and decimal field type recognition

**Performance Optimizations:**
- ⚡ **Computed Properties** - Efficient query building with Livewire computed properties
- 🔍 **Smart Search** - Optimized search queries with proper column targeting
- 📄 **Pagination** - Laravel's built-in pagination with customizable page sizes
- 🚀 **Lazy Loading** - Components load data only when needed

**Code Quality:**
- 🎯 **Type Safety** - Proper type hints and parameter validation
- 📝 **Documentation** - Comprehensive PHPDoc blocks and inline comments
- 🧪 **Tested** - Full test coverage for all generators and edge cases
- 🏗️ **PSR-4** - Proper namespace structure and autoloading

### 🎨 UI/UX Excellence

- **Tailwind CSS 4.x** - Latest Tailwind features and utilities
- **Mobile-First** - Responsive design that works on all devices  
- **Accessibility** - WCAG 2.1 compliant with proper ARIA labels
- **Dark Mode Ready** - Components adapt to dark/light themes
- **Loading States** - Skeleton loaders and smooth transitions
- **Error Handling** - User-friendly error messages and validation

## Testing

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Feature/MakeVoltFormCommandTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html=coverage
```

## Requirements

- **PHP** 8.2+ (with required extensions)
- **Laravel** 10.0+ | 11.0+ | 12.0+ (tested on Laravel 12)
- **Livewire** 3.0+ (for reactive components)
- **Laravel Volt** 1.0+ (for functional API syntax)
- **Tailwind CSS** 3.0+ (for styling, 4.x recommended)
- **Alpine.js** 3.0+ (for modal interactions)

### Optional Dependencies

- **Chart.js** 4.0+ (for chart components)
- **Laravel Pint** (for code formatting)
- **PHPStan** (for static analysis)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License