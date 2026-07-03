<?php

namespace Shoboske\DataTableQueryBuilder\Classes\Filters;

use Shoboske\DataTableQueryBuilder\Exceptions\RelationshipColumnsNotFoundException;
use Shoboske\DataTableQueryBuilder\Exceptions\RelationshipModelNotSetException;

class FilterBelongsToManyRelationships
{
    public function __invoke($query, $searchValue, $relationshipModelFactory, $model, $relationships)
    {
        if (isset($relationships['belongsToMany'])) {

            $searchTerm = config('data-table-query-builder.models.search_term');
            $likeTerm = config('data-table-query-builder.like_term');
            foreach ($relationships['belongsToMany'] as $tableName => $options) {

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

                $query = $query->orWhereHas($tableName, function ($query) use ($searchValue, $model, $options, $searchTerm, $likeTerm) {
                    // Get the real table name
                    $tableName = $model->getTable();

                    foreach ($options['columns'] as $columnName => $column) {
                        // Check if column is searchable
                        if ($column[$searchTerm]) {
                            // Check if first key
                            if ($columnName === key($options['columns'])) {
                                $query->where("$tableName.$columnName", $likeTerm, "%$searchValue%");
                            } else {
                                $query->orWhere("$tableName.$columnName", $likeTerm, "%$searchValue%");
                            }
                        }
                    }
                });
            }
        }

        return $query;
    }
}
