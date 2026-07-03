<?php

namespace Shoboske\DataTableQueryBuilder\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DataTableQueryTrait
{
    use DataTableQueryBuilderTrait;

    public function scopeDataTableQuery(Builder $query, ?array $additionalRelationships = [])
    {
        $takeKey = config('data-table-query-builder.query_params.take', 'take');
        $defaultTake = config('data-table-query-builder.query_params.default_take', 10);
        $skipKey = config('data-table-query-builder.query_params.skip', 'skip');
        $searchKey = config('data-table-query-builder.query_params.search', 'search');
        $sortKey = config('data-table-query-builder.query_params.sort', 'sort');
        $sortedTypeKey = config('data-table-query-builder.query_params.direction', 'direction');
        $defaultSortColumn = config('data-table-query-builder.models.default_sort_column_name', 'id');
        $dataKey = config('data-table-query-builder.response_keys.data', 'data');
        $countKey = config('data-table-query-builder.response_keys.count', 'count');

        $take = request()->input($takeKey) ?? $defaultTake;
        $skip = request()->input($skipKey) ?? 0;
        $search = request()->input($searchKey);
        $sortedKey = request()->input($sortKey) ?? $defaultSortColumn;
        $sortedType = request()->input($sortedTypeKey);

        $query = $this->scopeEloquentQuery(query: $query, columnName: $sortedKey, sortDirection: $sortedType, filter: $search, relationships: $additionalRelationships);

        $count = $query->get()->count();
        $data = $query->take($take)->skip($skip)->get();

        return [$dataKey => $data, $countKey => $count];
    }
}
