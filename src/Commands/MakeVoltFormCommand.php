<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class MakeVoltFormCommand extends Command
{
    protected $signature = 'make:volt-form {model : The model name}
                           {--action=create : Form action (create, edit, both)}';

    protected $description = 'Generate a Volt Form component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return 1;
        }

        $action = $this->option('action');
        if (!in_array($action, ['create', 'edit', 'both'])) {
            $this->error("Action must be one of: create, edit, both");
            return 1;
        }

        $componentPath = $this->getComponentPath($model, $action);
        $componentContent = $this->generateComponent($model, $action);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $this->info("Volt component [{$componentPath}] created successfully.");

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

    protected function getComponentPath(string $model, string $action): string
    {
        $basePath = config('wink-volt-generator.path', 'app/Livewire');
        $pluralModel = Str::plural($model);
        
        $filename = match ($action) {
            'create' => 'Create.php',
            'edit' => 'Edit.php',
            'both' => 'Form.php',
            default => 'Form.php',
        };
        
        return base_path("{$basePath}/{$pluralModel}/{$filename}");
    }

    protected function generateComponent(string $model, string $action): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $modelInstance = new $modelClass;
        
        $formFields = $this->generateFormFields($modelClass, $modelInstance);
        $validationRules = $this->generateValidationRules($modelClass, $modelInstance);
        $submitMethod = $this->generateSubmitMethod($modelClass, $action);
        $mountMethod = $this->generateMountMethod($action);
        $stateVariables = $this->generateStateVariables($modelInstance, $action);

        $pluralModel = Str::plural($model);
        $modelVariable = Str::camel($model);

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ model_name }}',
            '{{ model_variable }}',
            '{{ plural_model }}',
            '{{ action }}',
            '{{ form_fields }}',
            '{{ validation_rules }}',
            '{{ submit_method }}',
            '{{ mount_method }}',
            '{{ state_variables }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            $this->getComponentClass($action),
            $modelClass,
            $model,
            $modelVariable,
            Str::lower($pluralModel),
            $action,
            $formFields,
            $validationRules,
            $submitMethod,
            $mountMethod,
            $stateVariables,
        ], $stub);
    }

    protected function getComponentClass(string $action): string
    {
        return match ($action) {
            'create' => 'Create',
            'edit' => 'Edit',
            'both' => 'Form',
            default => 'Form',
        };
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-form.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-form.stub';
    }

    protected function generateFormFields(string $modelClass, Model $modelInstance): string
    {
        $table = $modelInstance->getTable();
        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
            $this->warn("Model {$modelClass} has no fillable attributes. Using all columns except excluded ones.");
            $allColumns = Schema::getColumnListing($table);
            $excludeColumns = config('wink-volt-generator.form.exclude_columns', [
                'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'
            ]);
            $fillable = array_diff($allColumns, $excludeColumns);
        }

        $fields = [];
        foreach ($fillable as $field) {
            $fieldType = $this->getFieldType($table, $field);
            $fields[] = $this->generateField($field, $fieldType);
        }

        return implode("\n\n", $fields);
    }

    protected function getFieldType(string $table, string $column): string
    {
        $columnType = Schema::getColumnType($table, $column);
        
        return match (true) {
            str_contains($column, 'email') => 'email',
            str_contains($column, 'password') => 'password',
            str_contains($column, 'phone') => 'tel',
            str_contains($column, 'url') || str_contains($column, 'website') => 'url',
            in_array($columnType, ['text', 'longtext']) => 'textarea',
            in_array($columnType, ['integer', 'bigint', 'decimal', 'float', 'double']) => 'number',
            in_array($columnType, ['date']) => 'date',
            in_array($columnType, ['datetime', 'timestamp']) => 'datetime-local',
            in_array($columnType, ['time']) => 'time',
            in_array($columnType, ['boolean']) => 'checkbox',
            default => 'text',
        };
    }

    protected function generateField(string $field, string $type): string
    {
        $label = Str::title(str_replace('_', ' ', $field));
        $wireModel = "form.{$field}";
        
        if ($type === 'textarea') {
            return "        <div>
            <label for=\"{$field}\" class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
            <textarea 
                wire:model=\"{$wireModel}\" 
                id=\"{$field}\"
                rows=\"4\"
                class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"
                placeholder=\"Enter {$label}\"
            ></textarea>
            @error('{$field}') <span class=\"text-red-500 text-xs mt-1\">{{ \$message }}</span> @enderror
        </div>";
        }
        
        if ($type === 'checkbox') {
            return "        <div class=\"flex items-center\">
            <input 
                wire:model=\"{$wireModel}\" 
                id=\"{$field}\"
                type=\"checkbox\"
                class=\"h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded\"
            >
            <label for=\"{$field}\" class=\"ml-2 block text-sm text-gray-900\">{$label}</label>
            @error('{$field}') <span class=\"text-red-500 text-xs ml-6\">{{ \$message }}</span> @enderror
        </div>";
        }

        return "        <div>
            <label for=\"{$field}\" class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
            <input 
                wire:model=\"{$wireModel}\" 
                type=\"{$type}\"
                id=\"{$field}\"
                class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"
                placeholder=\"Enter {$label}\"
            >
            @error('{$field}') <span class=\"text-red-500 text-xs mt-1\">{{ \$message }}</span> @enderror
        </div>";
    }

    protected function generateValidationRules(string $modelClass, Model $modelInstance): string
    {
        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
            $table = $modelInstance->getTable();
            $allColumns = Schema::getColumnListing($table);
            $excludeColumns = config('wink-volt-generator.form.exclude_columns', [
                'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'
            ]);
            $fillable = array_diff($allColumns, $excludeColumns);
        }

        // Try to get validation rules from model if it has a rules method
        if (method_exists($modelInstance, 'rules')) {
            $modelRules = $modelInstance->rules();
            $rules = [];
            foreach ($fillable as $field) {
                if (isset($modelRules[$field])) {
                    $rules[] = "        'form.{$field}' => '{$modelRules[$field]}',";
                } else {
                    $rules[] = "        'form.{$field}' => 'required',";
                }
            }
        } else {
            // Generate basic validation rules
            $rules = [];
            foreach ($fillable as $field) {
                $rule = $this->generateFieldValidationRule($modelInstance->getTable(), $field);
                $rules[] = "        'form.{$field}' => '{$rule}',";
            }
        }

        return implode("\n", $rules);
    }

    protected function generateFieldValidationRule(string $table, string $field): string
    {
        $columnType = Schema::getColumnType($table, $field);
        
        $rules = ['required'];
        
        if (str_contains($field, 'email')) {
            $rules[] = 'email';
        }
        
        if (in_array($columnType, ['integer', 'bigint'])) {
            $rules[] = 'integer';
        }
        
        if (in_array($columnType, ['decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        }
        
        if (in_array($columnType, ['date', 'datetime', 'timestamp'])) {
            $rules[] = 'date';
        }
        
        if ($columnType === 'boolean') {
            $rules = ['boolean'];
        }

        return implode('|', $rules);
    }

    protected function generateSubmitMethod(string $modelClass, string $action): string
    {
        $modelVariable = '$' . Str::camel(class_basename($modelClass));
        
        if ($action === 'create') {
            return "    {$modelVariable} = {$modelClass}::create(\$this->form);
        
        session()->flash('message', '{$this->getModelDisplayName($modelClass)} created successfully!');
        
        \$this->reset('form');";
        }
        
        if ($action === 'edit') {
            return "    \$this->model->update(\$this->form);
        
        session()->flash('message', '{$this->getModelDisplayName($modelClass)} updated successfully!');";
        }
        
        // Both action
        return "    if (\$this->model) {
            \$this->model->update(\$this->form);
            session()->flash('message', '{$this->getModelDisplayName($modelClass)} updated successfully!');
        } else {
            {$modelVariable} = {$modelClass}::create(\$this->form);
            session()->flash('message', '{$this->getModelDisplayName($modelClass)} created successfully!');
            \$this->reset('form');
        }";
    }

    protected function generateMountMethod(string $action): string
    {
        if ($action === 'create') {
            return '';
        }
        
        if ($action === 'edit') {
            return 'mount(function ({model_class} $model) {
    $this->model = $model;
    $this->form = $model->toArray();
});';
        }
        
        // Both action
        return 'mount(function ({model_class} $model = null) {
    $this->model = $model;
    $this->form = $model ? $model->toArray() : [];
});';
    }

    protected function generateStateVariables(Model $modelInstance, string $action): string
    {
        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
            $table = $modelInstance->getTable();
            $allColumns = Schema::getColumnListing($table);
            $excludeColumns = config('wink-volt-generator.form.exclude_columns', [
                'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'
            ]);
            $fillable = array_diff($allColumns, $excludeColumns);
        }

        $formFields = [];
        foreach ($fillable as $field) {
            $columnType = Schema::getColumnType($modelInstance->getTable(), $field);
            $defaultValue = match ($columnType) {
                'boolean' => 'false',
                'integer', 'bigint', 'decimal', 'float', 'double' => '0',
                default => "''",
            };
            $formFields[] = "    '{$field}' => {$defaultValue}";
        }

        $stateVars = ["'form' => [\n" . implode(",\n", $formFields) . "\n]"];
        
        if (in_array($action, ['edit', 'both'])) {
            $stateVars[] = "'model' => null";
        }

        return implode(', ', $stateVars);
    }

    protected function getModelDisplayName(string $modelClass): string
    {
        return Str::title(class_basename($modelClass));
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}