<?php

namespace Shoboske\DataTableQueryBuilder\Classes\Relationships;

class GetHasManyRelationships
{
    public function __invoke($declaredRelationship, $relationships, $with = [])
    {
        if (isset($declaredRelationship['hasMany'])) {

            $hasMany = array_keys($declaredRelationship['hasMany']);

            foreach ($hasMany as $key => $item) {
                if (in_array($item, $relationships)) {
                    $with[] = $item;
                }
            }

            return $with;
        }

        return $with;
    }
}
