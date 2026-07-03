<?php

namespace Shoboske\DataTableQueryBuilder\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Shoboske\DataTableQueryBuilder\DataTableQueryBuilderServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Shoboske\\DataTableQueryBuilder\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            DataTableQueryBuilderServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
