# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Feature/MakeVoltFormCommandTest.php

# Run specific test method
vendor/bin/phpunit --filter=it_can_generate_form_with_both_actions

# Run all feature tests
vendor/bin/phpunit tests/Feature/
```

### Development Testing - All Component Types
```bash
# Test DataTable generation
php artisan make:volt-datatable User

# Test Form generation with different actions
php artisan make:volt-form User --action=create
php artisan make:volt-form User --action=edit
php artisan make:volt-form User --action=both

# Test Modal generation with different types
php artisan make:volt-modal User --type=crud
php artisan make:volt-modal User --type=confirm
php artisan make:volt-modal User --type=view
php artisan make:volt-modal User --type=custom

# Test Card generation with different layouts
php artisan make:volt-card Product --layout=grid --columns=3
php artisan make:volt-card Product --layout=list
php artisan make:volt-card Product --layout=masonry --columns=4

# Test Search generation
php artisan make:volt-search Product
php artisan make:volt-search Product --fields=name,description --filters=category,status

# Test Chart generation with options
php artisan make:volt-chart Order --type=line --metric=sum --metric-column=total_price --time-unit=month

# Publish config for testing customization
php artisan vendor:publish --tag="wink-volt-generator-config"

# Publish stubs for testing template modifications
php artisan vendor:publish --tag="wink-volt-generator-stubs"
```

## Architecture Overview

### Package Structure
This is a comprehensive Laravel package that generates 6 different types of Laravel Volt components from Eloquent models: DataTables, Charts, Forms, Modals, Cards, and Search/Filter interfaces. The package follows Laravel's service provider pattern and uses Artisan commands for code generation.

**Recent Major Improvements (2024)**:
- ✅ **Fixed Template Issues**: Resolved {model_class} placeholder replacement in mount methods
- ✅ **Enhanced DataTables**: Added search, sorting, pagination, and CRUD action buttons
- ✅ **Smart Field Detection**: Boolean fields now generate as checkboxes, decimals include step attributes
- ✅ **Professional Modals**: Fixed Blade syntax issues and improved validation handling
- ✅ **Better Performance**: Optimized queries with computed properties and proper variable scoping

### Core Components

**Service Provider (`VoltGeneratorServiceProvider`)**:
- Registers six Artisan commands: `make:volt-datatable`, `make:volt-chart`, `make:volt-form`, `make:volt-modal`, `make:volt-card`, `make:volt-search`
- Handles config merging and publishable assets (config and stubs)
- Uses Laravel's auto-discovery for seamless installation

**Command Architecture**: All commands follow a consistent template method pattern:
1. **Model Resolution**: Validate model existence across multiple namespaces
2. **Introspection**: Extract model structure, fields, and relationships
3. **Content Generation**: Process stub templates with dynamic placeholders
4. **File Operations**: Create directories and write component files

**Component-Specific Patterns**:
- **DataTable**: Uses `Schema::getColumnListing()` to discover table columns, filters out configured exclusions
- **Chart**: Builds dynamic SQL queries based on metric type (count/sum/avg) and dimension type (regular/time-based)
- **Form**: Analyzes fillable attributes and generates appropriate input types with validation
- **Modal**: Creates different modal types with proper state management and Alpine.js integration
- **Card**: Generates responsive layouts with pagination and search integration
- **Search**: Creates filter interfaces with real-time search and multiple filter types

**Template System**:
- Uses stub files with placeholder replacement (`{{ model_name }}`, `{{ form_fields }}`, `{{ modal_content }}`, etc.)
- Each component type has specialized placeholders for its unique features
- Custom stubs can override defaults when published to `/stubs` directory
- All stubs generate Volt functional components with proper state management

### Key Patterns

**Model Resolution Strategy**: All commands try multiple namespaces in order:
1. Direct class name (if fully qualified)
2. `App\Models\{Model}`
3. `App\{Model}`

**Field Type Detection**: Forms and other components use intelligent field type detection:
- Column names (email, password, phone) → appropriate input types
- Database types (text → textarea, integer → number, boolean → checkbox)
- Date types → date/datetime-local inputs

**Query Generation Strategies**:
- **Chart**: Different strategies for count vs. aggregation queries, time-based grouping
- **Search**: Dynamic WHERE clauses with LIKE patterns for text search
- **Card**: Pagination and ordering with configurable limits

**Configuration System**: Comprehensive config cascade:
- Package defaults → published config → custom stubs (if published)
- Each component type has its own configuration section
- Column exclusions, styling, behavior all configurable per component

### Generated Component Architecture

All components use Laravel Volt's functional API consistently:
- **`state()`**: Component properties and reactive data
- **`mount()`**: Initialization logic and data loading
- **Blade sections**: HTML with Tailwind CSS styling and Alpine.js integration where needed

**State Management Patterns**:
- **Forms**: Field values, validation rules, loading states, mode switching (create/edit)
- **Modals**: Visibility state, form data, validation, action methods
- **Cards**: Pagination, search terms, layout configuration
- **Search**: Filter states, search terms, debounced input, result counts

### Testing Strategy
- Uses Orchestra Testbench for Laravel package testing
- Feature tests for all 6 component types with comprehensive coverage
- Tests verify file generation, content structure, option handling, error conditions
- Test models (User, Product) provided for consistent testing
- Proper cleanup in setUp/tearDown to avoid file conflicts
- Both positive and negative test cases (invalid models, missing parameters)

### Frontend Dependencies
- **Charts**: Require Chart.js (user must install separately)
- **Modals/Search**: Use Alpine.js for interactive behavior (included with Livewire)
- **All components**: Styled with Tailwind CSS classes
- **Forms**: Include CSRF protection and Livewire form handling

### Component Integration
Components are designed to work together:
- Search components can filter data for Card/DataTable components
- Modal components can contain Form components for CRUD operations
- All components emit Livewire events for parent-child communication
- Consistent styling and behavior across all component types

## Current Package Status (January 2025)

### ✅ Fully Working Features
- **All 6 generators**: DataTable, Form, Modal, Card, Search, Chart
- **Smart field detection**: Boolean → checkbox, decimal → number with step, text → textarea
- **Enhanced DataTables**: Search, sorting, pagination, CRUD actions, empty states
- **Professional modals**: Proper validation, dynamic titles, accessibility
- **Template system**: All placeholders correctly replaced
- **Laravel 12 compatibility**: Tested and working with latest Laravel/Livewire/Volt

### ⚡ Recent Fixes Applied
1. **Mount Method Placeholders**: Fixed `{model_class}` replacement in Form/Modal commands
2. **Variable Scoping**: DataTable now uses `$products` (collection) and `$product` (item)
3. **Field Type Detection**: Enhanced to recognize `tinyint` as boolean for `is_*` fields
4. **Modal Validation**: Fixed malformed PHP syntax in validation rule blocks
5. **Blade Output**: Modal titles now properly wrapped in `{{ }}` for dynamic content
6. **Step Attributes**: Number inputs for price/amount fields include `step="0.01"`

### 🎯 Component Quality
- **DataTable**: Production-ready with full CRUD, search, pagination
- **Forms**: Intelligent field detection, proper validation, loading states
- **Modals**: Accessible, responsive, proper state management
- **Cards/Search**: Responsive, configurable, performant
- **Charts**: Chart.js integration, multiple chart types
- **Stubs**: All templates generate clean, working code

### 🧪 Test Coverage
- All commands have comprehensive feature tests
- Generated components produce valid PHP/Blade code
- Template placeholders correctly replaced
- Error conditions properly handled
- Works with both simple and complex models