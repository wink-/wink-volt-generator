<?php

namespace Wink\VoltGenerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Wink\VoltGenerator\VoltGeneratorServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            VoltGeneratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}