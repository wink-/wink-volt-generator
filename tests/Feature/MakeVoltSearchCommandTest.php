<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltSearchCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up before each test
        if (File::exists(base_path('app/Livewire'))) {
            File::deleteDirectory(base_path('app/Livewire'));
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up after each test
        if (File::exists(base_path('app/Livewire'))) {
            File::deleteDirectory(base_path('app/Livewire'));
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_a_search_component_for_user_model()
    {
        $result = $this->artisan('make:volt-search', ['model' => 'User']);
        
        $result->assertExitCode(0);

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
        $result = $this->artisan('make:volt-search', [
            'model' => 'User',
            '--fields' => 'name,email',
            '--filters' => 'email_verified_at'
        ]);
        
        $result->assertExitCode(0);

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
        $result = $this->artisan('make:volt-search', ['model' => 'Product']);
        $result->assertExitCode(0);

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
}