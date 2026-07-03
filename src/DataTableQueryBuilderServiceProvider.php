<?php

namespace Shoboske\DataTableQueryBuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Shoboske\DataTableQueryBuilder\Commands\DataTableQueryBuilderCommand;

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
            ->hasConfigFile();
    }
}
