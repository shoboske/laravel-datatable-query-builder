<?php

namespace Shoboske\DataTableQueryBuilder\Contracts;

interface ExceptionHandlerContract
{
    public function checkForModel($model, $tableName);

    public function checkForForeignKey($key, $tableName);
}
