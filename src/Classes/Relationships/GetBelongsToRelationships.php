<?php

namespace Shoboske\DataTableQueryBuilder\Classes\Relationships;

class GetBelongsToRelationships
{
    public function __invoke($declaredRelationship, $relationships, $with = [])
    {
        if (isset($declaredRelationship['belongsTo'])) {

            $belongsTo = array_keys($declaredRelationship['belongsTo']);

            foreach ($belongsTo as $key => $item) {
                if (\in_array($item, $relationships)) {
                    $with[] = $item;
                }
            }

            return $with;
        }

        return $with;
    }
}
