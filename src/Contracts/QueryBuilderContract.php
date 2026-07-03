<?php

namespace Shoboske\DataTableQueryBuilder\Contracts;

interface QueryBuilderContract
{
    public function selectData();

    public function orderBy($columnName, $direction = 'asc');

    public function addRelationships($declaredRelationship, $sortDirection);

    public function filter($searchValue);
}
