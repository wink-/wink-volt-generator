<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltModalCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any test files
        $this->cleanupTestFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        
        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_crud_modal_component()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->expectsOutput('Volt modal component [app/Livewire/Users/CrudModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/CrudModal.php')));
        
        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('wire:model="form.name"', $content);
        $this->assertStringContainsString('wire:model="form.email"', $content);
        $this->assertStringContainsString('wire:model="form.password"', $content);
        $this->assertStringContainsString('$save = function', $content);
        $this->assertStringContainsString('User updated successfully!', $content);
        $this->assertStringContainsString('User created successfully!', $content);
    }

    /** @test */
    public function it_can_generate_confirm_modal_component()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->expectsOutput('Volt modal component [app/Livewire/Users/ConfirmModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/ConfirmModal.php')));
        
        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('Delete User', $content);
        $this->assertStringContainsString('Are you sure you want to delete', $content);
        $this->assertStringContainsString('$confirmDelete = function', $content);
        $this->assertStringContainsString('$this->model->delete()', $content);
        $this->assertStringContainsString('User deleted successfully!', $content);
    }

    /** @test */
    public function it_can_generate_view_modal_component()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->expectsOutput('Volt modal component [app/Livewire/Users/ViewModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/ViewModal.php')));
        
        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('<dl class="divide-y divide-gray-200">', $content);
        $this->assertStringContainsString('{{ $model->name ?? \'N/A\' }}', $content);
        $this->assertStringContainsString('{{ $model->email ?? \'N/A\' }}', $content);
        $this->assertStringContainsString('$closeModal = function', $content);
    }

    /** @test */
    public function it_can_generate_custom_modal_component()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'custom'])
             ->expectsOutput('Volt modal component [app/Livewire/Users/CustomModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/CustomModal.php')));
        
        $content = File::get(base_path('app/Livewire/Users/CustomModal.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('This is a custom modal for User', $content);
        $this->assertStringContainsString('Add your custom content here', $content);
        $this->assertStringContainsString('$handleAction = function', $content);
        $this->assertStringContainsString('Action completed successfully!', $content);
    }

    /** @test */
    public function it_defaults_to_crud_type_when_no_type_specified()
    {
        $this->artisan('make:volt-modal', ['model' => 'User'])
             ->expectsOutput('Volt modal component [app/Livewire/Users/CrudModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/CrudModal.php')));
    }

    /** @test */
    public function it_can_generate_modal_for_product_model()
    {
        $this->artisan('make:volt-modal', ['model' => 'Product', '--type' => 'crud'])
             ->expectsOutput('Volt modal component [app/Livewire/Products/CrudModal.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Products/CrudModal.php')));
        
        $content = File::get(base_path('app/Livewire/Products/CrudModal.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\Product', $content);
        $this->assertStringContainsString('wire:model="form.name"', $content);
        $this->assertStringContainsString('wire:model="form.description"', $content);
        $this->assertStringContainsString('wire:model="form.price"', $content);
        $this->assertStringContainsString('wire:model="form.stock"', $content);
    }

    /** @test */
    public function it_generates_proper_field_types_for_product_model()
    {
        $this->artisan('make:volt-modal', ['model' => 'Product', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Products/CrudModal.php'));
        
        // Check that description field uses textarea
        $this->assertStringContainsString('<textarea', $content);
        $this->assertStringContainsString('wire:model="form.description"', $content);
        
        // Check that price and stock fields use number type
        $this->assertStringContainsString('type="number"', $content);
        $this->assertStringContainsString('wire:model="form.price"', $content);
        $this->assertStringContainsString('wire:model="form.stock"', $content);
    }

    /** @test */
    public function it_generates_validation_rules_for_crud_and_custom_modals()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        $this->assertStringContainsString("'form.name' => 'required'", $content);
        $this->assertStringContainsString("'form.email' => 'required|email'", $content);
        $this->assertStringContainsString("'form.password' => 'required'", $content);
    }

    /** @test */
    public function it_does_not_generate_validation_rules_for_confirm_and_view_modals()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringNotContainsString("'form.name'", $content);

        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringNotContainsString("'form.name'", $content);
    }

    /** @test */
    public function it_generates_proper_state_variables_for_different_modal_types()
    {
        // CRUD modal should have form state
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString("'showModal' => false", $content);
        $this->assertStringContainsString("'model' => null", $content);
        $this->assertStringContainsString("'form' => [", $content);
        $this->assertStringContainsString("'name' => ''", $content);

        // Confirm modal should not have form state
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString("'showModal' => false", $content);
        $this->assertStringContainsString("'model' => null", $content);
        $this->assertStringNotContainsString("'form' => [", $content);

        // View modal should not have form state
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString("'showModal' => false", $content);
        $this->assertStringContainsString("'model' => null", $content);
        $this->assertStringNotContainsString("'form' => [", $content);
    }

    /** @test */
    public function it_generates_proper_mount_methods_for_different_modal_types()
    {
        // CRUD modal allows optional model
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString('mount(function', $content);
        $this->assertStringContainsString('if ($model)', $content);
        $this->assertStringContainsString('$this->form = $model->toArray()', $content);

        // Confirm modal requires model
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString('mount(function', $content);
        $this->assertStringContainsString('$this->model = $model', $content);

        // View modal requires model
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString('mount(function', $content);
        $this->assertStringContainsString('$this->model = $model', $content);
    }

    /** @test */
    public function it_generates_proper_modal_titles_for_different_types()
    {
        // CRUD modal has dynamic title
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString("'Edit User' : 'Create User'", $content);

        // Confirm modal has delete title
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString("'Delete User'", $content);

        // View modal has view title
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString("'View User'", $content);

        // Custom modal has generic title
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'custom'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CustomModal.php'));
        $this->assertStringContainsString("'User Modal'", $content);
    }

    /** @test */
    public function it_generates_proper_modal_sizes_for_different_types()
    {
        // CRUD modal uses max-w-2xl
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString('max-w-2xl', $content);

        // Confirm modal uses max-w-md (smaller)
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString('max-w-md', $content);

        // View modal uses max-w-3xl (larger)
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString('max-w-3xl', $content);
    }

    /** @test */
    public function it_generates_proper_methods_for_different_modal_types()
    {
        // CRUD modal has save method
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        $this->assertStringContainsString('$save = function', $content);
        $this->assertStringContainsString('$this->validate()', $content);
        $this->assertStringContainsString('$closeModal = function', $content);

        // Confirm modal has confirmDelete method
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        $this->assertStringContainsString('$confirmDelete = function', $content);
        $this->assertStringContainsString('$this->model->delete()', $content);
        $this->assertStringContainsString('$closeModal = function', $content);

        // View modal only has closeModal method
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ViewModal.php'));
        $this->assertStringContainsString('$closeModal = function', $content);
        $this->assertStringNotContainsString('$save = function', $content);
        $this->assertStringNotContainsString('$confirmDelete = function', $content);

        // Custom modal has handleAction method
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'custom'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CustomModal.php'));
        $this->assertStringContainsString('$handleAction = function', $content);
        $this->assertStringContainsString('$closeModal = function', $content);
    }

    /** @test */
    public function it_fails_for_non_existent_model()
    {
        $this->artisan('make:volt-modal', ['model' => 'NonExistentModel'])
             ->expectsOutput('Model App\NonExistentModel does not exist.')
             ->assertExitCode(1);

        $this->assertFalse(File::exists(base_path('app/Livewire/NonExistentModels/CrudModal.php')));
    }

    /** @test */
    public function it_fails_for_invalid_modal_type()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'invalid'])
             ->expectsOutput('Type must be one of: crud, confirm, view, custom')
             ->assertExitCode(1);

        $this->assertFalse(File::exists(base_path('app/Livewire/Users/CrudModal.php')));
    }

    /** @test */
    public function it_creates_directory_if_not_exists()
    {
        $this->assertFalse(File::isDirectory(base_path('app/Livewire/Users')));

        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $this->assertTrue(File::isDirectory(base_path('app/Livewire/Users')));
        $this->assertTrue(File::exists(base_path('app/Livewire/Users/CrudModal.php')));
    }

    /** @test */
    public function it_generates_proper_error_display_for_crud_modal_fields()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        $this->assertStringContainsString("@error('name')", $content);
        $this->assertStringContainsString("@error('email')", $content);
        $this->assertStringContainsString("@error('password')", $content);
        $this->assertStringContainsString('{{ $message }}', $content);
    }

    /** @test */
    public function it_generates_proper_labels_for_crud_modal_fields()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        $this->assertStringContainsString('Name</label>', $content);
        $this->assertStringContainsString('Email</label>', $content);
        $this->assertStringContainsString('Password</label>', $content);
    }

    /** @test */
    public function it_generates_proper_field_types_based_on_column_names_for_crud_modal()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        // Email field should have email type
        $this->assertStringContainsString('type="email"', $content);
        
        // Password field should have password type
        $this->assertStringContainsString('type="password"', $content);
    }

    /** @test */
    public function it_uses_test_models_in_test_environment()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        // Should reference the test model namespace in generated content
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('Wink\VoltGenerator\Tests\Models\User::create', $content);
    }

    /** @test */
    public function it_overwrites_existing_files()
    {
        // Create initial file
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $initialContent = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        // Modify the file
        File::put(base_path('app/Livewire/Users/CrudModal.php'), '<?php // Modified content');
        
        // Generate again
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'crud'])
             ->assertExitCode(0);

        $newContent = File::get(base_path('app/Livewire/Users/CrudModal.php'));
        
        // Should be the generated content, not the modified content
        $this->assertEquals($initialContent, $newContent);
        $this->assertStringNotContainsString('// Modified content', $newContent);
    }

    /** @test */
    public function it_generates_warning_icon_for_confirm_modal()
    {
        $this->artisan('make:volt-modal', ['model' => 'User', '--type' => 'confirm'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/ConfirmModal.php'));
        
        $this->assertStringContainsString('bg-red-100', $content);
        $this->assertStringContainsString('text-red-600', $content);
        $this->assertStringContainsString('<svg', $content);
    }

    /** @test */
    public function it_generates_view_modal_with_readonly_data_display()
    {
        $this->artisan('make:volt-modal', ['model' => 'Product', '--type' => 'view'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Products/ViewModal.php'));
        
        $this->assertStringContainsString('<dl class="divide-y divide-gray-200">', $content);
        $this->assertStringContainsString('<dt class="text-sm font-medium text-gray-500">', $content);
        $this->assertStringContainsString('<dd class="mt-1 text-sm text-gray-900">', $content);
        $this->assertStringContainsString('{{ $model->name ?? \'N/A\' }}', $content);
        $this->assertStringContainsString('{{ $model->description ?? \'N/A\' }}', $content);
        $this->assertStringContainsString('{{ $model->price ?? \'N/A\' }}', $content);
        $this->assertStringContainsString('{{ $model->stock ?? \'N/A\' }}', $content);
    }

    protected function cleanupTestFiles()
    {
        $paths = [
            base_path('app/Livewire/Users'),
            base_path('app/Livewire/Products'),
            base_path('app/Livewire/NonExistentModels'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }
    }
}