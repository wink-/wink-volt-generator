<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltDataTableCommandTest extends TestCase
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
    public function it_can_generate_datatable_component()
    {
        $this->artisan('make:volt-datatable', ['model' => 'User'])
             ->expectsOutput('Volt component [app/Livewire/Users/DataTable.php] created successfully.')
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/DataTable.php')));
    }

    /** @test */
    public function it_fails_for_non_existent_model()
    {
        $this->artisan('make:volt-datatable', ['model' => 'NonExistentModel'])
             ->expectsOutput('Model App\Models\NonExistentModel does not exist.')
             ->assertExitCode(1);
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