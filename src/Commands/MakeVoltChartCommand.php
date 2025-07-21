<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeVoltChartCommand extends Command
{
    protected $signature = 'make:volt-chart {model : The model name}
                           {--type=bar : Chart type (bar, line, pie, doughnut)}
                           {--dimension=created_at : Column for chart labels}
                           {--metric=count : Aggregation method (count, sum, avg)}
                           {--metric-column= : Column for sum/avg aggregations}
                           {--time-unit=day : Time grouping unit (day, month, year)}';

    protected $description = 'Generate a Volt Chart component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return 1;
        }

        $type = $this->option('type');
        $dimension = $this->option('dimension');
        $metric = $this->option('metric');
        $metricColumn = $this->option('metric-column');
        $timeUnit = $this->option('time-unit');

        if (in_array($metric, ['sum', 'avg']) && !$metricColumn) {
            $this->error("--metric-column is required when using sum or avg metrics.");
            return 1;
        }

        $componentPath = $this->getComponentPath($model);
        $componentContent = $this->generateComponent($model, $type, $dimension, $metric, $metricColumn, $timeUnit);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $this->info("Volt component [{$componentPath}] created successfully.");
        $this->warn("Don't forget to install Chart.js: npm install chart.js");

        return 0;
    }

    protected function getModelClass(string $model): string
    {
        if (class_exists($model)) {
            return $model;
        }

        $modelClass = "App\\Models\\{$model}";
        if (class_exists($modelClass)) {
            return $modelClass;
        }

        return "App\\{$model}";
    }

    protected function getComponentPath(string $model): string
    {
        $basePath = config('wink-volt-generator.path', 'app/Livewire');
        $pluralModel = Str::plural($model);
        
        return base_path("{$basePath}/{$pluralModel}/Chart.php");
    }

    protected function generateComponent(string $model, string $type, string $dimension, string $metric, ?string $metricColumn, string $timeUnit): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $pluralModel = Str::plural($model);
        $queryLogic = $this->generateQueryLogic($modelClass, $dimension, $metric, $metricColumn, $timeUnit);
        $chartColors = $this->getChartColors();

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ plural_model }}',
            '{{ chart_type }}',
            '{{ query_logic }}',
            '{{ chart_colors }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            'Chart',
            $modelClass,
            Str::lower($pluralModel),
            $type,
            $queryLogic,
            json_encode($chartColors),
        ], $stub);
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-chart.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-chart.stub';
    }

    protected function generateQueryLogic(string $modelClass, string $dimension, string $metric, ?string $metricColumn, string $timeUnit): string
    {
        $modelVariable = '$' . Str::camel(class_basename($modelClass));
        
        if ($metric === 'count') {
            if (in_array($dimension, ['created_at', 'updated_at'])) {
                return $this->generateTimeBasedQuery($modelClass, $dimension, $timeUnit, 'count');
            }
            
            return "{$modelVariable} = {$modelClass}::groupBy('{$dimension}')
            ->selectRaw('{$dimension}, COUNT(*) as count')
            ->get();
        
        \$this->labels = {$modelVariable}->pluck('{$dimension}')->toArray();
        \$this->data = {$modelVariable}->pluck('count')->toArray();";
        }

        if (in_array($dimension, ['created_at', 'updated_at'])) {
            return $this->generateTimeBasedQuery($modelClass, $dimension, $timeUnit, $metric, $metricColumn);
        }

        $aggregateFunction = strtoupper($metric);
        return "{$modelVariable} = {$modelClass}::groupBy('{$dimension}')
            ->selectRaw('{$dimension}, {$aggregateFunction}({$metricColumn}) as value')
            ->get();
        
        \$this->labels = {$modelVariable}->pluck('{$dimension}')->toArray();
        \$this->data = {$modelVariable}->pluck('value')->toArray();";
    }

    protected function generateTimeBasedQuery(string $modelClass, string $dimension, string $timeUnit, string $metric, ?string $metricColumn = null): string
    {
        $modelVariable = '$' . Str::camel(class_basename($modelClass));
        $dateFormat = $this->getDateFormat($timeUnit);
        
        if ($metric === 'count') {
            return "{$modelVariable} = {$modelClass}::selectRaw(\"DATE_FORMAT({$dimension}, '{$dateFormat}') as period, COUNT(*) as count\")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        \$this->labels = {$modelVariable}->pluck('period')->toArray();
        \$this->data = {$modelVariable}->pluck('count')->toArray();";
        }

        $aggregateFunction = strtoupper($metric);
        return "{$modelVariable} = {$modelClass}::selectRaw(\"DATE_FORMAT({$dimension}, '{$dateFormat}') as period, {$aggregateFunction}({$metricColumn}) as value\")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        \$this->labels = {$modelVariable}->pluck('period')->toArray();
        \$this->data = {$modelVariable}->pluck('value')->toArray();";
    }

    protected function getDateFormat(string $timeUnit): string
    {
        return match ($timeUnit) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };
    }

    protected function getChartColors(): array
    {
        return config('wink-volt-generator.chart.colors', [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
            '#F97316', '#06B6D4', '#84CC16', '#EC4899', '#6B7280'
        ]);
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}