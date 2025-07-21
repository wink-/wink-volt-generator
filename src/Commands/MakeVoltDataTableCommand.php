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

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ model_variable }}',
            '{{ plural_model }}',
            '{{ table_headers }}',
            '{{ table_rows }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            'DataTable',
            $modelClass,
            $modelVariable,
            Str::lower($pluralModel),
            $tableHeaders,
            $tableRows,
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

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}