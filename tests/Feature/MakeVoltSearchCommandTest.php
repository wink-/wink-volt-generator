<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltSearchCommandTest extends TestCase
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
    public function it_can_generate_a_search_component_for_user_model()
    {
        $this->artisan('make:volt-search', ['model' => 'User'])
             ->expectsOutput('Volt search component [app/Livewire/Users/Search.php] created successfully.')
             ->assertExitCode(0);

        $componentPath = base_path('app/Livewire/Users/Search.php');
        $this->assertTrue(File::exists($componentPath));

        $content = File::get($componentPath);
        
        // Check that the component contains expected content
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\User;', $content);
        $this->assertStringContainsString('$performSearch = function ()', $content);
        $this->assertStringContainsString('$clearSearch = function ()', $content);
        $this->assertStringContainsString('wire:model.live.debounce.300ms="search"', $content);
        $this->assertStringContainsString('Search users...', $content);
    }

    /** @test */
    public function it_can_generate_a_search_component_with_custom_fields()
    {
        $this->artisan('make:volt-search', [
            'model' => 'User',
            '--fields' => 'name,email',
            '--filters' => 'email_verified_at'
        ])
        ->expectsOutput('Volt search component [app/Livewire/Users/Search.php] created successfully.')
        ->assertExitCode(0);

        $componentPath = base_path('app/Livewire/Users/Search.php');
        $this->assertTrue(File::exists($componentPath));

        $content = File::get($componentPath);
        
        // Check that the component contains the specified fields
        $this->assertStringContainsString("->orWhere('name', 'like'", $content);
        $this->assertStringContainsString("->orWhere('email', 'like'", $content);
        $this->assertStringContainsString('email_verified_at_from', $content);
        $this->assertStringContainsString('email_verified_at_to', $content);
    }

    /** @test */
    public function it_shows_error_for_non_existent_model()
    {
        $result = $this->artisan('make:volt-search', ['model' => 'NonExistentModel']);
        $result->assertExitCode(1);
    }

    /** @test */
    public function it_can_generate_search_component_for_product_model()
    {
        $this->artisan('make:volt-search', ['model' => 'Product'])
             ->expectsOutput('Volt search component [app/Livewire/Products/Search.php] created successfully.')
             ->assertExitCode(0);

        $componentPath = base_path('app/Livewire/Products/Search.php');
        $this->assertTrue(File::exists($componentPath));

        $content = File::get($componentPath);
        
        // Check that the component contains expected content for Product
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\Product;', $content);
        $this->assertStringContainsString('Search products...', $content);
        
        // Should auto-detect searchable fields (name, description)
        $this->assertStringContainsString("->orWhere('name', 'like'", $content);
        $this->assertStringContainsString("->orWhere('description', 'like'", $content);
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