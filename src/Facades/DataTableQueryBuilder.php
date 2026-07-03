<?php

namespace Shoboske\DataTableQueryBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Shoboske\DataTableQueryBuilder\DataTableQueryBuilder
 */
class DataTableQueryBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Shoboske\DataTableQueryBuilder\DataTableQueryBuilder::class;
    }
}
