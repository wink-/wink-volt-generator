<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeVoltCardCommand extends Command
{
    protected $signature = 'make:volt-card {model : The model name} {--layout=grid : Layout type (grid, list, masonry)} {--columns=3 : Number of columns for grid layout}';

    protected $description = 'Generate a Volt Card component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $layout = $this->option('layout');
        $columns = $this->option('columns');

        if (!in_array($layout, ['grid', 'list', 'masonry'])) {
            $this->error("Invalid layout type. Supported layouts: grid, list, masonry");
            return 1;
        }

        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return 1;
        }

        $modelInstance = new $modelClass;
        $table = $modelInstance->getTable();
        $columns_data = Schema::getColumnListing($table);
        
        $excludeColumns = config('wink-volt-generator.card.exclude_columns', [
            'id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'
        ]);
        
        $displayColumns = array_diff($columns_data, $excludeColumns);

        $componentPath = $this->getComponentPath($model);
        $componentContent = $this->generateComponent($model, $displayColumns, $layout, $columns);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $relativePath = str_replace(base_path() . '/', '', $componentPath);
        $this->info("Volt card component [{$relativePath}] created successfully.");
        $this->info("Layout: {$layout}");
        if ($layout === 'grid') {
            $this->info("Grid columns: {$columns}");
        }

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
        
        return base_path("{$basePath}/{$pluralModel}/Cards.php");
    }

    protected function generateComponent(string $model, array $columns, string $layout, int $gridColumns): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $modelVariable = Str::camel($model);
        $pluralModel = Str::plural($model);
        $cardContent = $this->generateCardContent($columns, $modelVariable);
        $gridClasses = $this->getGridClasses($layout, $gridColumns);
        $cardClasses = $this->getCardClasses($layout);
        $layoutSpecificClasses = $this->getLayoutSpecificClasses($layout);

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ model_class_simple }}',
            '{{ model_variable }}',
            '{{ plural_model }}',
            '{{ card_content }}',
            '{{ grid_classes }}',
            '{{ card_classes }}',
            '{{ layout_specific_classes }}',
            '{{ layout }}',
            '{{ columns }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            'Cards',
            $modelClass,
            $model,
            $modelVariable,
            Str::lower($pluralModel),
            $cardContent,
            $gridClasses,
            $cardClasses,
            $layoutSpecificClasses,
            $layout,
            $gridColumns,
        ], $stub);
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-card.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-card.stub';
    }

    protected function generateCardContent(array $columns, string $modelVariable): string
    {
        $content = [];
        
        foreach ($columns as $column) {
            $label = Str::title(str_replace('_', ' ', $column));
            $content[] = "                        <div class=\"mb-2\">
                            <span class=\"text-sm font-medium text-gray-500\">{$label}:</span>
                            <p class=\"text-sm text-gray-900 mt-1\">{{ \${$modelVariable}->{$column} }}</p>
                        </div>";
        }

        return implode("\n", $content);
    }

    protected function getGridClasses(string $layout, int $columns): string
    {
        switch ($layout) {
            case 'grid':
                return "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{$columns} gap-6";
            case 'list':
                return "space-y-4";
            case 'masonry':
                return "columns-1 md:columns-2 lg:columns-{$columns} gap-6 space-y-6";
            default:
                return "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6";
        }
    }

    protected function getCardClasses(string $layout): string
    {
        $baseClasses = "bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200";
        
        switch ($layout) {
            case 'grid':
                return $baseClasses . " p-6";
            case 'list':
                return $baseClasses . " p-4 flex items-start space-x-4";
            case 'masonry':
                return $baseClasses . " p-4 break-inside-avoid mb-6";
            default:
                return $baseClasses . " p-6";
        }
    }

    protected function getLayoutSpecificClasses(string $layout): string
    {
        switch ($layout) {
            case 'list':
                return "flex-1";
            case 'masonry':
                return "w-full";
            default:
                return "";
        }
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}