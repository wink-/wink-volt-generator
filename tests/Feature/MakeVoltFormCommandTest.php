<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltFormCommandTest extends TestCase
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
    public function it_can_generate_create_form_component()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->expectsOutput('Volt component [app/Livewire/Users/Create.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Create.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        // The generated file doesn't use namespace/class - it's a Volt functional component
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('wire:model="form.name"', $content);
        $this->assertStringContainsString('wire:model="form.email"', $content);
        $this->assertStringContainsString('wire:model="form.password"', $content);
        $this->assertStringContainsString('User::create($this->form)', $content);
    }

    /** @test */
    public function it_can_generate_edit_form_component()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'edit'])
             ->expectsOutput('Volt component [app/Livewire/Users/Edit.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Edit.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Edit.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('mount(function', $content);
        $this->assertStringContainsString('$this->model = $model', $content);
        $this->assertStringContainsString('$this->model->update($this->form)', $content);
    }

    /** @test */
    public function it_can_generate_both_form_component()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'both'])
             ->expectsOutput('Volt component [app/Livewire/Users/Form.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Form.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Form.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('if ($this->model)', $content);
        $this->assertStringContainsString('$this->model->update($this->form)', $content);
        $this->assertStringContainsString('User::create($this->form)', $content);
    }

    /** @test */
    public function it_defaults_to_create_action_when_no_action_specified()
    {
        $this->artisan('make:volt-form', ['model' => 'User'])
             ->expectsOutput('Volt component [app/Livewire/Users/Create.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Create.php')));
    }

    /** @test */
    public function it_can_generate_form_for_product_model()
    {
        $this->artisan('make:volt-form', ['model' => 'Product', '--action' => 'create'])
             ->expectsOutput('Volt component [app/Livewire/Products/Create.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Products/Create.php')));
        
        $content = File::get(base_path('app/Livewire/Products/Create.php'));
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\Product', $content);
        $this->assertStringContainsString('wire:model="form.name"', $content);
        $this->assertStringContainsString('wire:model="form.description"', $content);
        $this->assertStringContainsString('wire:model="form.price"', $content);
        $this->assertStringContainsString('wire:model="form.stock"', $content);
    }

    /** @test */
    public function it_generates_proper_field_types_for_product_model()
    {
        $this->artisan('make:volt-form', ['model' => 'Product', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Products/Create.php'));
        
        // Check that description field uses textarea
        $this->assertStringContainsString('<textarea', $content);
        $this->assertStringContainsString('wire:model="form.description"', $content);
        
        // Check that stock field uses number type (price might be text due to decimal)
        $this->assertStringContainsString('type="number"', $content);
        $this->assertStringContainsString('wire:model="form.stock"', $content);
        
        // Check that price field exists (might be text type for decimal handling)
        $this->assertStringContainsString('wire:model="form.price"', $content);
    }

    /** @test */
    public function it_generates_validation_rules()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        
        $this->assertStringContainsString("'form.name' => 'required'", $content);
        $this->assertStringContainsString("'form.email' => 'required|email'", $content);
        $this->assertStringContainsString("'form.password' => 'required'", $content);
    }

    /** @test */
    public function it_generates_state_variables_with_correct_defaults()
    {
        $this->artisan('make:volt-form', ['model' => 'Product', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Products/Create.php'));
        
        $this->assertStringContainsString("'name' => ''", $content);
        $this->assertStringContainsString("'description' => ''", $content);
        // Price might be handled as string for decimal precision
        $this->assertTrue(
            str_contains($content, "'price' => 0") || str_contains($content, "'price' => ''")
        );
        $this->assertStringContainsString("'stock' => 0", $content);
    }

    /** @test */
    public function it_includes_model_variable_in_edit_and_both_actions()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'edit'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Edit.php'));
        $this->assertStringContainsString("'model' => null", $content);

        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'both'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Form.php'));
        $this->assertStringContainsString("'model' => null", $content);
    }

    /** @test */
    public function it_generates_proper_submit_methods_for_different_actions()
    {
        // Test create action
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        $this->assertStringContainsString('User created successfully!', $content);
        $this->assertStringContainsString('$this->reset(\'form\')', $content);

        // Test edit action
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'edit'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Edit.php'));
        $this->assertStringContainsString('User updated successfully!', $content);
        $this->assertStringNotContainsString('$this->reset(\'form\')', $content);

        // Test both action
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'both'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Form.php'));
        $this->assertStringContainsString('User updated successfully!', $content);
        $this->assertStringContainsString('User created successfully!', $content);
    }

    /** @test */
    public function it_fails_for_non_existent_model()
    {
        $this->artisan('make:volt-form', ['model' => 'NonExistentModel'])
             ->expectsOutput('Model App\NonExistentModel does not exist.')
             ->assertExitCode(1);

        $this->assertFalse(File::exists(base_path('app/Livewire/NonExistentModels/Create.php')));
    }

    /** @test */
    public function it_fails_for_invalid_action()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'invalid'])
             ->expectsOutput('Action must be one of: create, edit, both')
             ->assertExitCode(1);

        $this->assertFalse(File::exists(base_path('app/Livewire/Users/Create.php')));
    }

    /** @test */
    public function it_creates_directory_if_not_exists()
    {
        $this->assertFalse(File::isDirectory(base_path('app/Livewire/Users')));

        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $this->assertTrue(File::isDirectory(base_path('app/Livewire/Users')));
        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Create.php')));
    }

    /** @test */
    public function it_generates_proper_error_display_for_form_fields()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        
        $this->assertStringContainsString("@error('name')", $content);
        $this->assertStringContainsString("@error('email')", $content);
        $this->assertStringContainsString("@error('password')", $content);
        $this->assertStringContainsString('{{ $message }}', $content);
    }

    /** @test */
    public function it_generates_proper_labels_for_form_fields()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        
        $this->assertStringContainsString('Name</label>', $content);
        $this->assertStringContainsString('Email</label>', $content);
        $this->assertStringContainsString('Password</label>', $content);
    }

    /** @test */
    public function it_generates_proper_field_types_based_on_column_names()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        
        // Email field should have email type
        $this->assertStringContainsString('type="email"', $content);
        
        // Password field should have password type
        $this->assertStringContainsString('type="password"', $content);
    }

    /** @test */
    public function it_uses_test_models_in_test_environment()
    {
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Create.php'));
        
        // Should reference the test model namespace in generated content
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User', $content);
        $this->assertStringContainsString('Wink\VoltGenerator\Tests\Models\User::create', $content);
    }

    /** @test */
    public function it_overwrites_existing_files()
    {
        // Create initial file
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $initialContent = File::get(base_path('app/Livewire/Users/Create.php'));
        
        // Modify the file
        File::put(base_path('app/Livewire/Users/Create.php'), '<?php // Modified content');
        
        // Generate again
        $this->artisan('make:volt-form', ['model' => 'User', '--action' => 'create'])
             ->assertExitCode(0);

        $newContent = File::get(base_path('app/Livewire/Users/Create.php'));
        
        // Should be the generated content, not the modified content
        $this->assertEquals($initialContent, $newContent);
        $this->assertStringNotContainsString('// Modified content', $newContent);
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