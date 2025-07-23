<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeVoltDataTableCommand extends Command
{
    protected $signature = 'make:volt-datatable {model : The model name}';

    protected $description = 'Generate a Volt DataTable component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model App\\Models\\{$model} does not exist.");
            return 1;
        }

        $modelInstance = new $modelClass;
        $table = $modelInstance->getTable();
        $columns = Schema::getColumnListing($table);
        
        $excludeColumns = config('wink-volt-generator.datatable.exclude_columns', [
            'id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'
        ]);
        
        $displayColumns = array_diff($columns, $excludeColumns);

        $componentPath = $this->getComponentPath($model);
        $componentContent = $this->generateComponent($model, $displayColumns);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $relativePath = str_replace(base_path() . '/', '', $componentPath);
        $this->info("Volt component [{$relativePath}] created successfully.");

        return 0;
    }

    protected function getModelClass(string $model): string
    {
        if (class_exists($model)) {
            return $model;
        }

        // Check test models first (for testing)
        $testModelClass = "Wink\\VoltGenerator\\Tests\\Models\\{$model}";
        if (class_exists($testModelClass)) {
            return $testModelClass;
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
        
        return base_path("{$basePath}/{$pluralModel}/DataTable.php");
    }

    protected function generateComponent(string $model, array $columns): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $modelVariable = Str::camel($model);
        $pluralModel = Str::plural($model);
        $tableHeaders = $this->generateTableHeaders($columns);
        $tableRows = $this->generateTableRows($columns, $modelVariable);

        $searchFields = $this->generateSearchFields($columns, $modelVariable);
        $tableColspan = count($columns) + 1; // +1 for actions column

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ model_name }}',
            '{{ model_variable }}',
            '{{ plural_model_variable }}',
            '{{ plural_model }}',
            '{{ table_headers }}',
            '{{ table_rows }}',
            '{{ search_fields }}',
            '{{ table_colspan }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            'DataTable',
            $modelClass,
            $model,
            $modelVariable,
            Str::camel($pluralModel),
            Str::lower($pluralModel),
            $tableHeaders,
            $tableRows,
            $searchFields,
            $tableColspan,
        ], $stub);
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-datatable.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-datatable.stub';
    }

    protected function generateTableHeaders(array $columns): string
    {
        $headers = array_map(function ($column) {
            return '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . 
                   Str::title(str_replace('_', ' ', $column)) . '</th>';
        }, $columns);

        return implode("\n                    ", $headers);
    }

    protected function generateTableRows(array $columns, string $modelVariable): string
    {
        $cells = array_map(function ($column) use ($modelVariable) {
            return "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">{{ \${$modelVariable}->{$column} }}</td>";
        }, $columns);

        return implode("\n                        ", $cells);
    }

    protected function generateSearchFields(array $columns, string $modelVariable): string
    {
        // Filter columns that are likely to be searchable (text-based)
        $searchableColumns = array_filter($columns, function($column) {
            return in_array($column, ['name', 'title', 'description', 'email']) || 
                   str_contains($column, 'name') || 
                   str_contains($column, 'title') ||
                   str_contains($column, 'description');
        });

        if (empty($searchableColumns)) {
            // Fallback to first text-like column
            $searchableColumns = array_slice($columns, 0, 1);
        }

        $searchConditions = [];
        foreach ($searchableColumns as $column) {
            $searchConditions[] = "\$query->orWhere('{$column}', 'like', '%' . \$this->search . '%');";
        }

        return implode("\n        ", $searchConditions);
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}