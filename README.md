# Wink Volt Generator

🚀 **The ultimate Laravel package for rapid TALL stack development**

Generate complete, production-ready Laravel Volt components from your Eloquent models with a single command. Perfect for building DataTables, Charts, Forms, Modals, Card grids, and Search interfaces in seconds.

## Features

✨ **6 Powerful Generators:**
- 📊 **DataTables** - Responsive tables with sorting and styling
- 📈 **Charts** - Interactive Chart.js visualizations  
- 📝 **Forms** - Complete CRUD forms with validation
- 🪟 **Modals** - Accessible modal dialogs for any use case
- 🎴 **Cards** - Beautiful card/grid layouts
- 🔍 **Search** - Advanced search and filter interfaces

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

```bash
# Generate a complete user management suite
php artisan make:volt-datatable User
php artisan make:volt-form User --action=both  
php artisan make:volt-modal User --type=crud
php artisan make:volt-card User --layout=grid
php artisan make:volt-search User
```

## Component Generators

### 📊 DataTable Generator

Generate responsive, sortable data tables:

```bash
# Basic table
php artisan make:volt-datatable User

# Result: app/Livewire/Users/DataTable.php
```

**Features:** Automatic column detection, responsive design, Tailwind styling, configurable exclusions.

### 📝 Form Generator

Create comprehensive CRUD forms with validation:

```bash
# Create form
php artisan make:volt-form User --action=create

# Edit form  
php artisan make:volt-form User --action=edit

# Combined create/edit form
php artisan make:volt-form User --action=both
```

**Features:** Smart field types, automatic validation, loading states, error handling, CSRF protection.

### 🪟 Modal Generator

Build accessible, interactive modals:

```bash
# CRUD operations modal
php artisan make:volt-modal User --type=crud

# Confirmation dialog
php artisan make:volt-modal User --type=confirm

# Read-only view modal
php artisan make:volt-modal User --type=view

# Custom modal template
php artisan make:volt-modal User --type=custom
```

**Features:** Alpine.js integration, ARIA accessibility, backdrop controls, responsive sizing, loading states.

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

- PHP 8.2+
- Laravel 10.0+ | 11.0+ | 12.0+
- Laravel Volt
- Livewire 3.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License