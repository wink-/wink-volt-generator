<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class MakeVoltSearchCommand extends Command
{
    protected $signature = 'make:volt-search {model : The model name}
                           {--fields= : Comma-separated list of searchable fields}
                           {--filters= : Comma-separated list of filterable fields}';

    protected $description = 'Generate a Volt Search/Filter component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return 1;
        }

        $componentPath = $this->getComponentPath($model);
        $componentContent = $this->generateComponent($model);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $this->info("Volt search component [{$componentPath}] created successfully.");

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
        
        return base_path("{$basePath}/{$pluralModel}/Search.php");
    }

    protected function generateComponent(string $model): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $modelInstance = new $modelClass;
        
        $searchableFields = $this->getSearchableFields($modelClass, $modelInstance);
        $filterableFields = $this->getFilterableFields($modelClass, $modelInstance);
        $filterInputs = $this->generateFilterInputs($modelClass, $modelInstance, $filterableFields);
        $searchLogic = $this->generateSearchLogic($modelClass, $searchableFields, $filterableFields);
        $stateVariables = $this->generateStateVariables($searchableFields, $filterableFields);
        $resetMethod = $this->generateResetMethod($searchableFields, $filterableFields);

        $pluralModel = Str::plural($model);
        $modelVariable = Str::camel($model);

        return str_replace([
            '{{ namespace }}',
            '{{ model_class }}',
            '{{ model_name }}',
            '{{ model_variable }}',
            '{{ plural_model }}',
            '{{ search_fields }}',
            '{{ filter_fields }}',
            '{{ filter_inputs }}',
            '{{ search_logic }}',
            '{{ state_variables }}',
            '{{ reset_method }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            $modelClass,
            $model,
            $modelVariable,
            Str::lower($pluralModel),
            implode(', ', array_map(fn($field) => "'{$field}'", $searchableFields)),
            implode(', ', array_map(fn($field) => "'{$field}'", $filterableFields)),
            $filterInputs,
            $searchLogic,
            $stateVariables,
            $resetMethod,
        ], $stub);
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-search.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-search.stub';
    }

    protected function getSearchableFields(string $modelClass, Model $modelInstance): array
    {
        $fieldsOption = $this->option('fields');
        
        if ($fieldsOption) {
            return array_map('trim', explode(',', $fieldsOption));
        }

        // Try to auto-detect searchable fields from fillable first
        $fillable = $modelInstance->getFillable();
        
        if (!empty($fillable)) {
            // Filter fillable fields to likely searchable ones
            $searchableFields = [];
            foreach ($fillable as $field) {
                // Include likely text fields based on name
                if (str_contains($field, 'name') || 
                    str_contains($field, 'title') || 
                    str_contains($field, 'description') ||
                    str_contains($field, 'email') ||
                    str_contains($field, 'username') ||
                    str_contains($field, 'slug') ||
                    (!str_contains($field, '_id') && 
                     !in_array($field, ['password', 'token', 'api_key']))) {
                    $searchableFields[] = $field;
                }
            }
            
            if (!empty($searchableFields)) {
                return $searchableFields;
            }
        }

        // Fallback: try to get from database schema if available
        try {
            $table = $modelInstance->getTable();
            $columns = Schema::getColumnListing($table);
            
            $searchableFields = [];
            foreach ($columns as $column) {
                $columnType = Schema::getColumnType($table, $column);
                
                // Include text-based columns for search
                if (in_array($columnType, ['string', 'text', 'longtext'])) {
                    $searchableFields[] = $column;
                }
            }

            // Exclude system columns
            $excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];
            return array_diff($searchableFields, $excludeColumns);
        } catch (\Exception $e) {
            // If schema introspection fails, return common fields
            return ['name', 'title', 'description'];
        }
    }

    protected function getFilterableFields(string $modelClass, Model $modelInstance): array
    {
        $filtersOption = $this->option('filters');
        
        if ($filtersOption) {
            return array_map('trim', explode(',', $filtersOption));
        }

        // Try to auto-detect filterable fields from fillable first
        $fillable = $modelInstance->getFillable();
        
        if (!empty($fillable)) {
            $filterableFields = [];
            foreach ($fillable as $field) {
                // Include likely filterable fields based on name
                if (str_contains($field, '_id') || 
                    str_contains($field, 'status') ||
                    str_contains($field, 'type') ||
                    str_contains($field, 'category') ||
                    str_contains($field, 'active') ||
                    str_contains($field, 'enabled')) {
                    $filterableFields[] = $field;
                }
            }
            
            if (!empty($filterableFields)) {
                return $filterableFields;
            }
        }

        // Fallback: try to get from database schema if available
        try {
            $table = $modelInstance->getTable();
            $columns = Schema::getColumnListing($table);
            
            $filterableFields = [];
            foreach ($columns as $column) {
                $columnType = Schema::getColumnType($table, $column);
                
                // Include fields suitable for filtering
                if (in_array($columnType, ['boolean', 'date', 'datetime', 'timestamp']) || 
                    str_contains($column, '_id') || 
                    str_contains($column, 'status') ||
                    str_contains($column, 'type') ||
                    str_contains($column, 'category')) {
                    $filterableFields[] = $column;
                }
            }

            // Exclude system columns
            $excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];
            return array_diff($filterableFields, $excludeColumns);
        } catch (\Exception $e) {
            // If schema introspection fails, return empty array
            return [];
        }
    }

    protected function generateFilterInputs(string $modelClass, Model $modelInstance, array $filterableFields): string
    {
        if (empty($filterableFields)) {
            return '';
        }

        $table = $modelInstance->getTable();
        $inputs = [];

        foreach ($filterableFields as $field) {
            $columnType = Schema::getColumnType($table, $field);
            $label = Str::title(str_replace('_', ' ', $field));
            
            $input = match (true) {
                $columnType === 'boolean' => $this->generateBooleanFilter($field, $label),
                in_array($columnType, ['date', 'datetime', 'timestamp']) => $this->generateDateFilter($field, $label),
                str_contains($field, '_id') || str_contains($field, 'status') || str_contains($field, 'type') => $this->generateSelectFilter($field, $label),
                default => $this->generateTextFilter($field, $label),
            };
            
            $inputs[] = $input;
        }

        return implode("\n\n", $inputs);
    }

    protected function generateBooleanFilter(string $field, string $label): string
    {
        return "                        <div>
                            <label class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
                            <select wire:model.live=\"filters.{$field}\" class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\">
                                <option value=\"\">All</option>
                                <option value=\"1\">Yes</option>
                                <option value=\"0\">No</option>
                            </select>
                        </div>";
    }

    protected function generateDateFilter(string $field, string $label): string
    {
        return "                        <div class=\"grid grid-cols-2 gap-2\">
                            <div>
                                <label class=\"block text-sm font-medium text-gray-700 mb-2\">{$label} From</label>
                                <input 
                                    wire:model.live=\"filters.{$field}_from\" 
                                    type=\"date\"
                                    class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"
                                >
                            </div>
                            <div>
                                <label class=\"block text-sm font-medium text-gray-700 mb-2\">{$label} To</label>
                                <input 
                                    wire:model.live=\"filters.{$field}_to\" 
                                    type=\"date\"
                                    class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"
                                >
                            </div>
                        </div>";
    }

    protected function generateSelectFilter(string $field, string $label): string
    {
        return "                        <div>
                            <label class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
                            <select wire:model.live=\"filters.{$field}\" class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\">
                                <option value=\"\">All</option>
                                <!-- Add options dynamically based on your data -->
                            </select>
                        </div>";
    }

    protected function generateTextFilter(string $field, string $label): string
    {
        return "                        <div>
                            <label class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
                            <input 
                                wire:model.live.debounce.300ms=\"filters.{$field}\"
                                type=\"text\"
                                placeholder=\"Filter by {$label}\"
                                class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"
                            >
                        </div>";
    }

    protected function generateSearchLogic(string $modelClass, array $searchableFields, array $filterableFields): string
    {
        $logic = [];
        
        // Search logic
        if (!empty($searchableFields)) {
            $searchConditions = [];
            foreach ($searchableFields as $field) {
                $searchConditions[] = "->orWhere('{$field}', 'like', '%' . \$this->search . '%')";
            }
            
            $logic[] = "// Apply search
        if (!empty(\$this->search)) {
            \$query->where(function (\$q) {
                \$q" . implode("\n                  ", $searchConditions) . ";
            });
        }";
        }
        
        // Filter logic
        if (!empty($filterableFields)) {
            $filterConditions = [];
            foreach ($filterableFields as $field) {
                if (str_contains($field, 'date') || str_contains($field, 'created') || str_contains($field, 'updated')) {
                    $filterConditions[] = "// Filter by {$field}
        if (!empty(\$this->filters['{$field}_from'])) {
            \$query->whereDate('{$field}', '>=', \$this->filters['{$field}_from']);
        }
        if (!empty(\$this->filters['{$field}_to'])) {
            \$query->whereDate('{$field}', '<=', \$this->filters['{$field}_to']);
        }";
                } else {
                    $filterConditions[] = "if (!empty(\$this->filters['{$field}']) && \$this->filters['{$field}'] !== '') {
            \$query->where('{$field}', \$this->filters['{$field}']);
        }";
                }
            }
            
            if (!empty($filterConditions)) {
                $logic[] = "// Apply filters\n        " . implode("\n        \n        ", $filterConditions);
            }
        }
        
        return implode("\n        \n        ", $logic);
    }

    protected function generateStateVariables(array $searchableFields, array $filterableFields): string
    {
        $stateVars = [
            "'search' => ''",
            "'results' => []",
            "'loading' => false",
            "'showFilters' => false",
        ];

        // Add filters state
        $filterDefaults = [];
        foreach ($filterableFields as $field) {
            if (str_contains($field, 'date') || str_contains($field, 'created') || str_contains($field, 'updated')) {
                $filterDefaults[] = "        '{$field}_from' => ''";
                $filterDefaults[] = "        '{$field}_to' => ''";
            } else {
                $filterDefaults[] = "        '{$field}' => ''";
            }
        }

        if (!empty($filterDefaults)) {
            $stateVars[] = "'filters' => [\n" . implode(",\n", $filterDefaults) . "\n    ]";
        } else {
            $stateVars[] = "'filters' => []";
        }

        return implode(', ', $stateVars);
    }

    protected function generateResetMethod(array $searchableFields, array $filterableFields): string
    {
        $resetItems = ["'search'", "'results'"];
        
        foreach ($filterableFields as $field) {
            if (str_contains($field, 'date') || str_contains($field, 'created') || str_contains($field, 'updated')) {
                $resetItems[] = "'filters.{$field}_from'";
                $resetItems[] = "'filters.{$field}_to'";
            } else {
                $resetItems[] = "'filters.{$field}'";
            }
        }

        return implode(', ', $resetItems);
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}