# A package for managing datatable queries with sorting, filtering, and server-side pagination

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shoboske/laravel-data-table-query-builder.svg?style=flat-square)](https://packagist.org/packages/shoboske/laravel-data-table-query-builder)
[![GitHub Tests Action Status](https://github.com/shoboske/laravel-data-table-query-builder/actions/workflows/run-tests.yml/badge.svg)](https://github.com/shoboske/laravel-data-table-query-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/shoboske/laravel-data-table-query-builder/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/shoboske/laravel-data-table-query-builder/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/shoboske/laravel-data-table-query-builder.svg?style=flat-square)](https://packagist.org/packages/shoboske/laravel-data-table-query-builder)

This package adds a reusable Eloquent scope for building datatable queries. It supports selecting searchable columns, applying relationship-aware filtering, ordering by a requested column, and eager-loading relationships used by your datatable.

## Installation

You can install the package via composer:

```bash
composer require shoboske/laravel-data-table-query-builder
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="data-table-query-builder-config"
```

This is the contents of the published config file:

```php
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
```

## Usage

Add the `DataTableQueryBuilderTrait` to your model and define the two required methods.

```php
use Illuminate\Database\Eloquent\Model;
use Shoboske\DataTableQueryBuilder\Traits\DataTableQueryBuilderTrait;

class User extends Model
{
	use DataTableQueryBuilderTrait;

	protected function getDataTableColumns(): array
	{
		return [
			'name' => [
				'searchable' => true,
			],
			'email' => [
				'searchable' => true,
			],
		];
	}

	protected function getDataTableRelationships(): array
	{
		return [
            "belongsTo" => [
                "role" => [
                    "model" => 'role_id'
                    'columns' => [
                        'role' => [
                            'searchable' => true,
                            'orderable' => true
                        ]
                    ]
                ]
            ]
        ];
	}
}
```

You can generate those methods automatically with the included command:

```bash
php artisan data-table:add-trait App\\Models\\User
```

Then use the scope in your controller or query layer:

```php
$users = User::query()
	->eloquentQuery('name', 'asc', request('search'))
	->get();
```

If you need to add relationships for sorting or filtering, return them from `getDataTableRelationships()` and pass the relationship names to the scope as the last argument.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Inspiration

This package was inspired by [James Dordoy's Laravel Vue Datatable package](https://github.com/jamesdordoy/Laravel-Vue-Datatable_Laravel-Package).

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Michael Shobowale](https://github.com/shoboske)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
