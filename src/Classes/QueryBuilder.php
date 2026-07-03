<?php

namespace Shoboske\DataTableQueryBuilder\Classes;

// Casts
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
// Contracts
use Shoboske\DataTableQueryBuilder\Classes\Factories\RelationshipModelFactory;
// Factories
use Shoboske\DataTableQueryBuilder\Classes\Filters\FilterBelongsToManyRelationships;
// Filters
use Shoboske\DataTableQueryBuilder\Classes\Filters\FilterBelongsToRelationships;
use Shoboske\DataTableQueryBuilder\Classes\Filters\FilterHasManyRelationships;
use Shoboske\DataTableQueryBuilder\Classes\Filters\FilterLocalData;
use Shoboske\DataTableQueryBuilder\Classes\Joins\JoinBelongsToManyRelationships;
// Joins
use Shoboske\DataTableQueryBuilder\Classes\Joins\JoinBelongsToRelationships;
use Shoboske\DataTableQueryBuilder\Classes\Joins\JoinHasManyRelationships;
use Shoboske\DataTableQueryBuilder\Classes\Relationships\GetBelongsToManyRelationships;
// Relationships
use Shoboske\DataTableQueryBuilder\Classes\Relationships\GetBelongsToRelationships;
use Shoboske\DataTableQueryBuilder\Classes\Relationships\GetHasManyRelationships;
use Shoboske\DataTableQueryBuilder\Contracts\QueryBuilderContract;
// Exceptions
use Shoboske\DataTableQueryBuilder\Exceptions\RelationshipForeignKeyNotSetException;

/**
 * Builds an Eloquent query with support for selecting columns,
 * joining relationships, sorting, filtering, and eager loading
 * for DataTable responses.
 */
class QueryBuilder implements QueryBuilderContract
{
    protected Model $model;

    protected Builder $query;

    /** @var array<string, mixed> */
    protected array $localColumns;

    /** @var array<string, mixed> */
    protected array $relationships;

    protected RelationshipModelFactory $relationshipModelFactory;

    /**
     * @param  array<string>  $localColumns
     * @param  array<string>  $relationships
     */
    public function __construct(Model $model, Builder $query, array $localColumns = [], array $relationships = [])
    {
        $this->model = $model;
        $this->query = $query;
        $this->localColumns = $localColumns;
        $this->relationships = $relationships;
        $this->relationshipModelFactory = new RelationshipModelFactory;
    }

    public function selectData(): QueryBuilder
    {
        // Select local data
        $columnKeys = $this->selectModelColumns();
        $columnKeys = $this->selectLocalForeignKeysForJoining($columnKeys);

        $joinBelongsTo = new JoinBelongsToRelationships;
        $this->query = $joinBelongsTo($this->query, $this->model, $this->relationships, $this->relationshipModelFactory);

        $joinHasMany = new JoinHasManyRelationships;
        $this->query = $joinHasMany($this->query, $this->model, $this->relationships, $this->relationshipModelFactory);

        $joinBelongMany = new JoinBelongsToManyRelationships;
        $this->query = $joinBelongMany($this->query, $this->model, $this->relationships, $this->relationshipModelFactory);

        if (\count($columnKeys)) {
            $this->query = $this->query->select($columnKeys);
        }

        $this->query->groupBy($this->model->getTable().'.'.$this->model->getKeyName());

        return $this;
    }

    public function orderBy($columnName, $sortDirection = 'asc'): QueryBuilder
    {
        $sortDirection = $sortDirection ?? 'asc';

        if (isset($columnName) && ! empty($columnName)) {
            $defaultOrderBy = config('data-table-query-builder.models.default_sort_column_name');
            $tableAndColumn = \count(explode('.', $columnName)) > 1 ? $columnName : $this->model->getTable().".$columnName";
            $this->query->orderBy($tableAndColumn, $sortDirection);
        } else {
            $defaultOrderBy = config('data-table-query-builder.default_sort_direction');
            $defaultOrderBy = $defaultOrderBy == null ? 'id' : $defaultOrderBy;
            $this->query->orderBy($this->model->getTable().".$defaultOrderBy", $sortDirection);
        }

        return $this;
    }

    public function addRelationships($declaredRelationship, $sortDirection): QueryBuilder
    {
        $getBelongsTo = new GetBelongsToRelationships;
        $with = $getBelongsTo($this->relationships, $declaredRelationship);

        $getHasMany = new GetHasManyRelationships;
        $with = $getHasMany($this->relationships, $declaredRelationship, $with);

        $getBelongsToMany = new GetBelongsToManyRelationships;
        $with = $getBelongsToMany($this->relationships, $declaredRelationship, $with, $sortDirection);

        if (\count($with)) {
            $this->query->with($with);
        }

        return $this;
    }

    public function filter($searchValue): QueryBuilder
    {
        if (isset($searchValue) && ! empty($searchValue)) {

            $filterLocalData = new FilterLocalData;
            $this->query = $filterLocalData($this->query, $searchValue, $this->model, $this->localColumns);

            $filterBelongsTo = new FilterBelongsToRelationships;
            $this->query = $filterBelongsTo($this->query, $searchValue, $this->relationshipModelFactory, $this->model, $this->relationships);

            $filterHasMany = new FilterHasManyRelationships;
            $this->query = $filterHasMany($this->query, $searchValue, $this->relationshipModelFactory, $this->model, $this->relationships);

            $filterBelongsToMany = new FilterBelongsToManyRelationships;
            $this->query = $filterBelongsToMany($this->query, $searchValue, $this->relationshipModelFactory, $this->model, $this->relationships);

            return $this;
        }

        return $this;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Build the list of columns to select from the model.
     *
     * @return array<string>
     */
    protected function selectModelColumns(): array
    {
        if (isset($this->localColumns) && ! empty($this->localColumns)) {

            $columnKeys = array_keys($this->localColumns);

            foreach ($columnKeys as $index => $key) {
                $defaultAs = config('data-table-query-builder.models.alias');
                $as = isset($this->localColumns[$key][$defaultAs]) ? ' as '.$this->localColumns[$key][$defaultAs] : '';

                $columnKeys[$index] = $this->model->getTable().".$key".$as;
            }

            return $columnKeys;
        }

        return [];
    }

    /**
     * Add local foreign keys required for relationship joins.
     *
     * @param  array<string>  $columnKeys
     * @return array<string>
     *
     * @throws RelationshipForeignKeyNotSetException
     */
    protected function selectLocalForeignKeysForJoining($columnKeys)
    {
        if (isset($this->relationships['belongsTo'])) {
            foreach ($this->relationships['belongsTo'] as $tableName => $options) {
                if (! isset($options['foreign_key'])) {
                    throw new RelationshipForeignKeyNotSetException(
                        "Foreign Key not set on relationship: $tableName"
                    );
                }

                $columnKeys[\count($columnKeys) + 1] = $this->model->getTable().'.'.$options['foreign_key'];
            }
        }

        return $columnKeys;
    }
}
