<?php

namespace Manusiakemos\WireCrud\Tests;

use Manusiakemos\WireCrud\WirecrudServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WirecrudServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

    }
}
