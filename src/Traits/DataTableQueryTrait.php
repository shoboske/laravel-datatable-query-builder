<?php

namespace Shoboske\DataTableQueryBuilder\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DataTableQueryTrait
{
    use DataTableQueryBuilderTrait;

    public function scopeDataTableQuery(Builder $query)
    {
        $takeKey = config('data-table-query-builder.query_params.take', 'take');
        $skipKey = config('data-table-query-builder.query_params.skip', 'skip');
        $searchKey = config('data-table-query-builder.query_params.search', 'search');
        $sortedKey = config('data-table-query-builder.query_params.sort', 'sort');
        $sortedTypeKey = config('data-table-query-builder.query_params.direction', 'direction');
        $dataKey = config('data-table-query-builder.response_keys.data', 'data');
        $countKey = config('data-table-query-builder.response_keys.count', 'count');

        $take = request()->input($takeKey) ?? 10;
        $skip = request()->input($skipKey) ?? 0;
        $search = request()->input($searchKey);
        $sortedType = request()->input($sortedTypeKey);

        $query = $this->scopeEloquentQuery(query: $query, columnName: $sortedKey, sortDirection: $sortedType, filter: $search);

        $count = $query->get()->count();
        $data = $query->take($take)->skip($skip)->get();

        return [$dataKey => $data, $countKey => $count];
    }
}
