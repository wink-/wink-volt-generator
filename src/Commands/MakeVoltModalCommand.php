<?php

namespace Wink\VoltGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class MakeVoltModalCommand extends Command
{
    protected $signature = 'make:volt-modal {model : The model name}
                           {--type=crud : Modal type (crud, confirm, view, custom)}';

    protected $description = 'Generate a Volt Modal component for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return 1;
        }

        $type = $this->option('type');
        if (!in_array($type, ['crud', 'confirm', 'view', 'custom'])) {
            $this->error("Type must be one of: crud, confirm, view, custom");
            return 1;
        }

        $componentPath = $this->getComponentPath($model, $type);
        $componentContent = $this->generateComponent($model, $type);

        $this->ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $componentContent);

        $relativePath = str_replace(base_path() . '/', '', $componentPath);
        $this->info("Volt modal component [{$relativePath}] created successfully.");

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

    protected function getComponentPath(string $model, string $type): string
    {
        $basePath = config('wink-volt-generator.path', 'app/Livewire');
        $pluralModel = Str::plural($model);
        
        $filename = match ($type) {
            'crud' => 'CrudModal.php',
            'confirm' => 'ConfirmModal.php',
            'view' => 'ViewModal.php',
            'custom' => 'CustomModal.php',
            default => 'Modal.php',
        };
        
        return base_path("{$basePath}/{$pluralModel}/{$filename}");
    }

    protected function generateComponent(string $model, string $type): string
    {
        $stubPath = $this->getStubPath();
        $stub = File::get($stubPath);

        $modelClass = $this->getModelClass($model);
        $modelInstance = new $modelClass;
        
        $modalContent = $this->generateModalContent($modelClass, $modelInstance, $type);
        $modalMethods = $this->generateModalMethods($modelClass, $type);
        $stateVariables = $this->generateStateVariables($modelInstance, $type);
        $validationRules = $this->generateValidationRules($modelClass, $modelInstance, $type);
        $mountMethod = $this->generateMountMethod($type);

        $pluralModel = Str::plural($model);
        $modelVariable = Str::camel($model);

        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
            '{{ model_class }}',
            '{{ model_name }}',
            '{{ model_variable }}',
            '{{ plural_model }}',
            '{{ modal_type }}',
            '{{ modal_content }}',
            '{{ modal_methods }}',
            '{{ state_variables }}',
            '{{ validation_rules }}',
            '{{ mount_method }}',
            '{{ mount_method_import }}',
            '{{ modal_title }}',
            '{{ modal_size }}',
        ], [
            "App\\Livewire\\{$pluralModel}",
            $this->getComponentClass($type),
            $modelClass,
            $model,
            $modelVariable,
            Str::lower($pluralModel),
            $type,
            $modalContent,
            $modalMethods,
            $stateVariables,
            $validationRules,
            $mountMethod,
            !empty($mountMethod) ? ', mount' : '',
            $this->getModalTitle($model, $type),
            $this->getModalSize($type),
        ], $stub);
    }

    protected function getComponentClass(string $type): string
    {
        return match ($type) {
            'crud' => 'CrudModal',
            'confirm' => 'ConfirmModal',
            'view' => 'ViewModal',
            'custom' => 'CustomModal',
            default => 'Modal',
        };
    }

    protected function getStubPath(): string
    {
        $customStub = base_path('stubs/volt-modal.stub');
        
        if (File::exists($customStub)) {
            return $customStub;
        }

        return __DIR__.'/../../stubs/volt-modal.stub';
    }

    protected function generateModalContent(string $modelClass, Model $modelInstance, string $type): string
    {
        return match ($type) {
            'crud' => $this->generateCrudContent($modelClass, $modelInstance),
            'confirm' => $this->generateConfirmContent($modelClass),
            'view' => $this->generateViewContent($modelClass, $modelInstance),
            'custom' => $this->generateCustomContent($modelClass),
            default => $this->generateCustomContent($modelClass),
        };
    }

    protected function generateCrudContent(string $modelClass, Model $modelInstance): string
    {
        $table = $modelInstance->getTable();
        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
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

    protected function generateConfirmContent(string $modelClass): string
    {
        $modelName = $this->getModelDisplayName($modelClass);
        
        return "                <div class=\"text-center\">
                    <div class=\"mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4\">
                        <svg class=\"h-6 w-6 text-red-600\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z\" />
                        </svg>
                    </div>
                    <h3 class=\"text-lg font-medium text-gray-900 mb-2\">Delete {$modelName}</h3>
                    <p class=\"text-sm text-gray-500 mb-4\">
                        Are you sure you want to delete this {$modelName}? This action cannot be undone.
                    </p>
                    @if(\$model)
                        <p class=\"text-sm font-medium text-gray-700 bg-gray-50 px-3 py-2 rounded\">
                            {{ \$model->name ?? \$model->title ?? \$model->id }}
                        </p>
                    @endif
                </div>";
    }

    protected function generateViewContent(string $modelClass, Model $modelInstance): string
    {
        $table = $modelInstance->getTable();
        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
            $allColumns = Schema::getColumnListing($table);
            $excludeColumns = config('wink-volt-generator.form.exclude_columns', [
                'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'
            ]);
            $fillable = array_diff($allColumns, $excludeColumns);
        }

        $fields = [];
        foreach ($fillable as $field) {
            $label = Str::title(str_replace('_', ' ', $field));
            $fields[] = "                <div class=\"border-b border-gray-200 py-3\">
                    <dt class=\"text-sm font-medium text-gray-500\">{$label}</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ \$model->{$field} ?? 'N/A' }}</dd>
                </div>";
        }

        return "                <dl class=\"divide-y divide-gray-200\">\n" . implode("\n\n", $fields) . "\n                </dl>";
    }

    protected function generateCustomContent(string $modelClass): string
    {
        return "                <div class=\"space-y-4\">
                    <p class=\"text-gray-600\">
                        This is a custom modal for {$this->getModelDisplayName($modelClass)}. 
                        Add your custom content here.
                    </p>
                    <!-- Add your custom modal content here -->
                </div>";
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
            return "                <div>
                    <label for=\"{$field}\" class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
                    <textarea 
                        wire:model=\"{$wireModel}\" 
                        id=\"{$field}\"
                        rows=\"4\"
                        class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500\"
                        placeholder=\"Enter {$label}\"
                    ></textarea>
                    @error('{$field}') <span class=\"text-red-500 text-xs mt-1\">{{ \$message }}</span> @enderror
                </div>";
        }
        
        if ($type === 'checkbox') {
            return "                <div class=\"flex items-center\">
                    <input 
                        wire:model=\"{$wireModel}\" 
                        id=\"{$field}\"
                        type=\"checkbox\"
                        class=\"h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded\"
                    >
                    <label for=\"{$field}\" class=\"ml-2 block text-sm text-gray-900\">{$label}</label>
                    @error('{$field}') <span class=\"text-red-500 text-xs ml-6\">{{ \$message }}</span> @enderror
                </div>";
        }

        return "                <div>
                    <label for=\"{$field}\" class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}</label>
                    <input 
                        wire:model=\"{$wireModel}\" 
                        type=\"{$type}\"
                        id=\"{$field}\"
                        class=\"w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500\"
                        placeholder=\"Enter {$label}\"
                    >
                    @error('{$field}') <span class=\"text-red-500 text-xs mt-1\">{{ \$message }}</span> @enderror
                </div>";
    }

    protected function generateModalMethods(string $modelClass, string $type): string
    {
        return match ($type) {
            'crud' => $this->generateCrudMethods($modelClass),
            'confirm' => $this->generateConfirmMethods($modelClass),
            'view' => $this->generateViewMethods(),
            'custom' => $this->generateCustomMethods($modelClass),
            default => $this->generateCustomMethods($modelClass),
        };
    }

    protected function generateCrudMethods(string $modelClass): string
    {
        return "\$save = function () {
    \$this->validate();
    
    if (\$this->model) {
        \$this->model->update(\$this->form);
        \$this->dispatch('modal-closed', ['message' => '{$this->getModelDisplayName($modelClass)} updated successfully!']);
    } else {
        {$modelClass}::create(\$this->form);
        \$this->dispatch('modal-closed', ['message' => '{$this->getModelDisplayName($modelClass)} created successfully!']);
    }
    
    \$this->closeModal();
};

\$closeModal = function () {
    \$this->showModal = false;
    \$this->reset('form');
};";
    }

    protected function generateConfirmMethods(string $modelClass): string
    {
        return "\$confirmDelete = function () {
    if (\$this->model) {
        \$this->model->delete();
        \$this->dispatch('modal-closed', ['message' => '{$this->getModelDisplayName($modelClass)} deleted successfully!']);
    }
    
    \$this->closeModal();
};

\$closeModal = function () {
    \$this->showModal = false;
};";
    }

    protected function generateViewMethods(): string
    {
        return "\$closeModal = function () {
    \$this->showModal = false;
};";
    }

    protected function generateCustomMethods(string $modelClass): string
    {
        return "\$handleAction = function () {
    // Add your custom action logic here
    \$this->dispatch('modal-closed', ['message' => 'Action completed successfully!']);
    \$this->closeModal();
};

\$closeModal = function () {
    \$this->showModal = false;
};";
    }

    protected function generateStateVariables(Model $modelInstance, string $type): string
    {
        $stateVars = ["'showModal' => false", "'model' => null"];
        
        if (in_array($type, ['crud', 'custom'])) {
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
                $formFields[] = "        '{$field}' => {$defaultValue}";
            }

            $stateVars[] = "'form' => [\n" . implode(",\n", $formFields) . "\n    ]";
        }

        return implode(', ', $stateVars);
    }

    protected function generateValidationRules(string $modelClass, Model $modelInstance, string $type): string
    {
        if (!in_array($type, ['crud', 'custom'])) {
            return '';
        }

        $fillable = $modelInstance->getFillable();
        
        if (empty($fillable)) {
            $table = $modelInstance->getTable();
            $allColumns = Schema::getColumnListing($table);
            $excludeColumns = config('wink-volt-generator.form.exclude_columns', [
                'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'
            ]);
            $fillable = array_diff($allColumns, $excludeColumns);
        }

        $rules = [];
        foreach ($fillable as $field) {
            $rule = $this->generateFieldValidationRule($modelInstance->getTable(), $field);
            $rules[] = "    'form.{$field}' => '{$rule}',";
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

    protected function generateMountMethod(string $type): string
    {
        return match ($type) {
            'crud' => 'mount(function ({model_class} $model = null) {
    $this->model = $model;
    if ($model) {
        $this->form = $model->toArray();
    }
});',
            'confirm', 'view' => 'mount(function ({model_class} $model) {
    $this->model = $model;
});',
            'custom' => 'mount(function ({model_class} $model = null) {
    $this->model = $model;
});',
            default => '',
        };
    }

    protected function getModalTitle(string $model, string $type): string
    {
        $modelName = $this->getModelDisplayName($model);
        
        return match ($type) {
            'crud' => "\$model ? 'Edit {$modelName}' : 'Create {$modelName}'",
            'confirm' => "'Delete {$modelName}'",
            'view' => "'View {$modelName}'",
            'custom' => "'{$modelName} Modal'",
            default => "'{$modelName} Modal'",
        };
    }

    protected function getModalSize(string $type): string
    {
        return match ($type) {
            'crud' => 'max-w-2xl',
            'confirm' => 'max-w-md',
            'view' => 'max-w-3xl',
            'custom' => 'max-w-2xl',
            default => 'max-w-2xl',
        };
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