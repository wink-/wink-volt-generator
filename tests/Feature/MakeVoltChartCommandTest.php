<?php

namespace Wink\VoltGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Wink\VoltGenerator\Tests\TestCase;

class MakeVoltChartCommandTest extends TestCase
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
    public function it_can_generate_chart_component()
    {
        $this->artisan('make:volt-chart', ['model' => 'User'])
             ->expectsOutput('Volt component [app/Livewire/Users/Chart.php] created successfully.')
             ->expectsOutput("Don't forget to install Chart.js: npm install chart.js")
             ->assertExitCode(0);

        $this->assertTrue(File::exists(base_path('app/Livewire/Users/Chart.php')));
    }

    /** @test */
    public function it_can_generate_chart_with_options()
    {
        $this->artisan('make:volt-chart', [
            'model' => 'User',
            '--type' => 'line',
            '--metric' => 'sum',
            '--metric-column' => 'total_amount',
        ])->assertExitCode(0);

        $content = File::get(base_path('app/Livewire/Users/Chart.php'));
        $this->assertStringContainsString('line', $content);
        $this->assertStringContainsString('SUM(total_amount)', $content);
    }

    /** @test */
    public function it_fails_when_metric_column_missing_for_sum()
    {
        $this->artisan('make:volt-chart', [
            'model' => 'User',
            '--metric' => 'sum',
        ])->expectsOutput('--metric-column is required when using sum or avg metrics.')
          ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_for_non_existent_model()
    {
        $this->artisan('make:volt-chart', ['model' => 'NonExistentModel'])
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