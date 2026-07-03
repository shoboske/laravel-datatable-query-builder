<?php

namespace Shoboske\DataTableQueryBuilder\Traits;

use Illuminate\Database\Eloquent\Builder;
use Shoboske\DataTableQueryBuilder\Classes\QueryBuilder;

/**
 * Provides a reusable Eloquent scope for building DataTable queries.
 */
trait DataTableQueryBuilderTrait
{
    /**
     * Build an Eloquent query with sorting, filtering, and relationships
     * for DataTable responses.
     *
     * @param  'asc'|'desc'  $sortDirection
     * @param  array<int, string>  $relationships
     * @return Builder
     */
    public function scopeEloquentQuery(
        Builder $query,
        string $columnName = 'id',
        string $sortDirection = 'asc',
        string $filter = '',
        array $relationships = []
    ) {
        $queryBuilder = new QueryBuilder(
            $this,
            $query,
            $this->dataTableColumns,
            $this->dataTableRelationships
        );

        return $queryBuilder->selectData()
            ->addRelationships($relationships, $sortDirection)
            ->orderBy($columnName, $sortDirection)
            ->filter($filter)
            ->query();
    }
}
