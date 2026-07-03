<?php

namespace Shoboske\DataTableQueryBuilder\Exceptions;

use Shoboske\DataTableQueryBuilder\Contracts\ExceptionHandlerContract;

class ExceptionHandler implements ExceptionHandlerContract
{
    public function checkForModel($model, $tableName) {}

    public function checkForForeignKey($key, $tableName)
    {
        if (! isset($key)) {
            throw new RelationshipForeignKeyNotSetException(
                "Foreign Key not set on relationship: $tableName"
            );
        }

        return true;
    }
}
