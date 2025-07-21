<?php

namespace Wink\VoltGenerator;

use Illuminate\Support\ServiceProvider;
use Wink\VoltGenerator\Commands\MakeVoltDataTableCommand;
use Wink\VoltGenerator\Commands\MakeVoltChartCommand;
use Wink\VoltGenerator\Commands\MakeVoltFormCommand;

class VoltGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/wink-volt-generator.php',
            'wink-volt-generator'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeVoltDataTableCommand::class,
                MakeVoltChartCommand::class,
                MakeVoltFormCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/wink-volt-generator.php' => config_path('wink-volt-generator.php'),
            ], 'wink-volt-generator-config');

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs'),
            ], 'wink-volt-generator-stubs');
        }
    }
}