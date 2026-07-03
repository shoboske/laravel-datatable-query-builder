<?php

namespace Shoboske\DataTableQueryBuilder\Traits;

use Shoboske\DataTableQueryBuilder\Classes\QueryBuilder;

trait DataTableQueryBuilderTrait
{
    public function scopeEloquentQuery($query, $columnName = 'id', $direction = 'asc', $searchValue = '', $relationships = [])
    {
        $queryBuilder = new QueryBuilder($this, $query, $this->dataTableColumns, $this->dataTableRelationships);

        return $queryBuilder->selectData()
            ->addRelationships($relationships, $direction)
            ->orderBy($columnName, $direction)
            ->filter($searchValue)
            ->getQuery();
    }
}
