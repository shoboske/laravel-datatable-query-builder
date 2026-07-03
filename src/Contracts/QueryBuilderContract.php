<?php

namespace Shoboske\DataTableQueryBuilder\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface QueryBuilderContract
{
    /**
     * Build the base query.
     *
     * @return mixed
     */
    public function selectData(): QueryBuilderContract;

    /**
     * Apply an ORDER BY clause.
     *
     * @param  'asc'|'desc'  $sortDirection
     * @return mixed
     */
    public function orderBy(?string $columnName, string $sortDirection = 'asc'): QueryBuilderContract;

    /**
     * Apply relationship joins/eager loads required for sorting.
     *
     * @param  'asc'|'desc'  $sortDirection
     * @return mixed
     */
    public function addRelationships(string $declaredRelationship, string $sortDirection): QueryBuilderContract;

    /**
     * Apply search/filter constraints to the query.
     *
     * @return mixed
     */
    public function filter(?string $searchValue): QueryBuilderContract;

    /**
     * Get the underlying query builder instance.
     */
    public function query(): Builder;
}
