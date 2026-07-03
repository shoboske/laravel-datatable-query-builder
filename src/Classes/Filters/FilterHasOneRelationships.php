<?php

namespace Shoboske\DataTableQueryBuilder\Classes\Filters;

use Shoboske\DataTableQueryBuilder\Exceptions\RelationshipColumnsNotFoundException;
use Shoboske\DataTableQueryBuilder\Exceptions\RelationshipModelNotSetException;

class FilterHasOneRelationships
{
    public function __invoke($query, $searchValue, $relationshipModelFactory, $model, $relationships)
    {
        $searchTerm = config('data-table-query-builder.models.search_term');
        $likeTerm = config('data-table-query-builder.like_term');

        if (isset($relationships['hasOne'])) {

            foreach ($relationships['hasOne'] as $tableName => $options) {

                if (! isset($options['model'])) {
                    throw new RelationshipModelNotSetException(
                        "Model not set on relationship: $tableName"
                    );
                }

                if (! isset($options['columns'])) {
                    throw new RelationshipColumnsNotFoundException(
                        "Columns array not set on relationship: $tableName"
                    );
                }

                $model = $relationshipModelFactory($options['model'], $tableName);

                $query->orWhereHas($tableName, function ($query) use ($searchValue, $model, $options, $searchTerm, $likeTerm) {

                    if (isset($options['columns'])) {

                        $tableName = $model->getTable();

                        foreach ($options['columns'] as $columnName => $col) {
                            if ($col[$searchTerm]) {
                                if ($columnName === key($options['columns'])) {
                                    $query->where("$tableName.$columnName", $likeTerm, "%$searchValue%");
                                } else {
                                    $query->orWhere("$tableName.$columnName", $likeTerm, "%$searchValue%");
                                }
                            }
                        }
                    }
                });
            }
        }

        return $query;
    }
}
