<?php

namespace Shoboske\DataTableQueryBuilder;

use Shoboske\DataTableQueryBuilder\Console\Commands\AddDataTableQueryBuilderTraitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DataTableQueryBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-data-table-query-builder')
            ->hasConfigFile()
            ->hasCommands([
                AddDataTableQueryBuilderTraitCommand::class,
            ]);
    }
}
