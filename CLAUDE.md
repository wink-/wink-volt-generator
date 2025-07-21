# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Feature/MakeVoltDataTableCommandTest.php

# Run specific test method
vendor/bin/phpunit --filter=it_can_generate_datatable_component
```

### Development Testing
```bash
# Test DataTable generation locally
php artisan make:volt-datatable User

# Test Chart generation with options
php artisan make:volt-chart Order --type=line --metric=sum --metric-column=total_price --time-unit=month

# Publish config for testing customization
php artisan vendor:publish --tag="wink-volt-generator-config"

# Publish stubs for testing template modifications
php artisan vendor:publish --tag="wink-volt-generator-stubs"
```

## Architecture Overview

### Package Structure
This is a Laravel package that generates Laravel Volt components (DataTables and Charts) from Eloquent models. The package follows Laravel's service provider pattern and uses Artisan commands for code generation.

### Core Components

**Service Provider (`VoltGeneratorServiceProvider`)**:
- Registers two Artisan commands (`make:volt-datatable`, `make:volt-chart`)
- Handles config merging and publishable assets (config and stubs)
- Uses Laravel's auto-discovery for seamless installation

**Command Architecture**:
- Both commands follow a template method pattern: validate model → introspect/build query → generate from stub → write file
- **DataTable Command**: Uses `Schema::getColumnListing()` to discover table columns, filters out configured exclusions
- **Chart Command**: Builds dynamic SQL queries based on metric type (count/sum/avg) and dimension type (regular/time-based)

**Template System**:
- Uses stub files with placeholder replacement (`{{ model_name }}`, `{{ table_headers }}`, etc.)
- **DataTable stub**: Generates responsive HTML tables with Tailwind CSS styling
- **Chart stub**: Integrates Chart.js with Alpine.js for reactive behavior
- Custom stubs can override defaults when published to `/stubs` directory

### Key Patterns

**Model Resolution Strategy**: Commands try multiple namespaces in order:
1. Direct class name (if fully qualified)
2. `App\Models\{Model}`
3. `App\{Model}`

**Query Generation**: Chart command uses different strategies based on:
- Metric type: count queries vs. aggregation (sum/avg) queries
- Dimension type: regular columns vs. time-based columns with DATE_FORMAT grouping
- Time units: day/month/year affect the DATE_FORMAT pattern used

**Configuration Cascade**: 
- Package defaults → published config → custom stubs (if published)
- Column exclusions, colors, paths all configurable

### Generated Component Structure
Components use Laravel Volt's functional API:
- `state()` for component properties
- `mount()` for initialization logic
- Blade section for HTML with Alpine.js integration (charts only)

### Testing Strategy
- Uses Orchestra Testbench for Laravel package testing
- Feature tests verify actual file generation and command output
- Tests include cleanup in setUp/tearDown to avoid file conflicts
- Error conditions tested (missing models, invalid parameters)

### Frontend Dependencies
Generated chart components require Chart.js (user must install separately). DataTable components only need Tailwind CSS classes to render properly.