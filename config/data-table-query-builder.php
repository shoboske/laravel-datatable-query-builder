<?php

// config for Shoboske/DataTableQueryBuilder
return [
    // SQL operator used when filtering searchable columns.
    'like_term' => 'like',

    // Default sort direction when no explicit direction is provided.
    'default_sort_direction' => 'asc',

    'models' => [
        // Attribute name used to mark columns as searchable in a model.
        'search_term' => 'searchable',
        // Attribute name used when a column should be selected under an alias.
        'alias' => 'alias',
        // Default column used when no sort column is supplied.
        'default_sort_column_name' => 'id',
    ],
    'query_params' => [
        // Query parameter that controls how many records are returned.
        'take' => 'take',
        // Query parameter that controls how many records are skipped.
        'skip' => 'skip',
        // Query parameter that contains the search term.
        'search' => 'search',
        // Query parameter that contains the column to sort by.
        'sort' => 'sort',
        // Query parameter that contains the sort direction.
        'direction' => 'direction',
    ],
    'response_keys' => [
        // Response key that contains the paginated data.
        'data' => 'data',
        // Response key that contains the total result count.
        'count' => 'count',
    ],
];
