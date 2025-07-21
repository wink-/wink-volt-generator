# Wink Volt Generator

Laravel package for generating Volt components with DataTables and Charts from Eloquent models.

## Installation

```bash
composer require wink/volt-generator
```

## Frontend Setup (for Charts)

```bash
npm install chart.js
```

Add to your `resources/js/app.js`:
```javascript
import Chart from 'chart.js/auto';
window.Chart = Chart;
```

## Usage

### Generate DataTable Component

```bash
php artisan make:volt-datatable User
```

### Generate Chart Component

```bash
php artisan make:volt-chart Order --type=line --metric=sum --metric-column=total_price --time-unit=month
```

### Options for Charts

- `--type`: Chart type (bar, line, pie, doughnut) - default: bar
- `--dimension`: Column for chart labels - default: created_at
- `--metric`: Aggregation method (count, sum, avg) - default: count
- `--metric-column`: Column for sum/avg aggregations
- `--time-unit`: Time grouping unit (day, month, year) - default: day

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="wink-volt-generator-config"
```

Publish the stub files for customization:

```bash
php artisan vendor:publish --tag="wink-volt-generator-stubs"
```

## Using Generated Components

```html
<!-- Display the users table -->
<livewire:users.data-table />

<!-- Display the orders chart -->
<livewire:orders.chart />
```

## Testing

```bash
vendor/bin/phpunit
```

## License

MIT License