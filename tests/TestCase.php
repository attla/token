<?php

namespace Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Token' => \Attla\Token\Facade::class,
        ];
    }
}
