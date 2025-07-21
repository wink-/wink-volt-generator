<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltCardCommandTest extends TestCase
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
    public function it_can_generate_card_component_with_default_options()
    {
        $this->artisan('make:volt-card', ['model' => 'User'])
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Cards.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Cards.php'));
        $this->assertStringContainsString('grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6', $content);
    }

    /** @test */
    public function it_can_generate_card_component_with_list_layout()
    {
        $this->artisan('make:volt-card', ['model' => 'User', '--layout' => 'list'])
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Cards.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Cards.php'));
        $this->assertStringContainsString('space-y-4', $content);
    }

    /** @test */
    public function it_can_generate_card_component_with_masonry_layout()
    {
        $this->artisan('make:volt-card', ['model' => 'User', '--layout' => 'masonry', '--columns' => '4'])
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Cards.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Cards.php'));
        $this->assertStringContainsString('columns-1 md:columns-2 lg:columns-4 gap-6 space-y-6', $content);
    }

    /** @test */
    public function it_can_generate_card_component_with_custom_columns()
    {
        $this->artisan('make:volt-card', ['model' => 'User', '--columns' => '5'])
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Cards.php')));
        
        $content = File::get(base_path('app/Livewire/Users/Cards.php'));
        $this->assertStringContainsString('grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6', $content);
    }

    /** @test */
    public function it_fails_for_invalid_layout()
    {
        $this->artisan('make:volt-card', ['model' => 'User', '--layout' => 'invalid'])
             ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_for_non_existent_model()
    {
        $this->artisan('make:volt-card', ['model' => 'NonExistentModel'])
             ->assertExitCode(1);
    }

    /** @test */
    public function it_generates_proper_namespace_and_class()
    {
        $this->artisan('make:volt-card', ['model' => 'Product'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Products/Cards.php'));
        
        // Check that proper model class is imported (using test model)
        $this->assertStringContainsString('use Wink\VoltGenerator\Tests\Models\Product;', $content);
        
        // Check that proper variable names are used
        $this->assertStringContainsString('state([', $content);
        $this->assertStringContainsString('product', $content);
        $this->assertStringContainsString('products', $content);
    }

    /** @test */
    public function it_includes_required_features_in_generated_component()
    {
        $this->artisan('make:volt-card', ['model' => 'User'])
             ->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Cards.php'));
        
        // Check for key features
        $this->assertStringContainsString('search', $content);
        $this->assertStringContainsString('pagination', $content);
        $this->assertStringContainsString('loading', $content);
        $this->assertStringContainsString('animate-pulse', $content); // Loading skeleton
        $this->assertStringContainsString('View', $content); // Action buttons
        $this->assertStringContainsString('Edit', $content);
        $this->assertStringContainsString('Delete', $content);
        $this->assertStringContainsString('hover:shadow-md', $content); // Hover effects
        $this->assertStringContainsString('transition-', $content); // Transitions
    }

    protected function cleanupTestFiles()
    {
        $paths = [
            base_path('app/Livewire/Users'),
            base_path('app/Livewire/Products'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }
    }
}