<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shoboske\DataTableQueryBuilder\Traits\DataTableQueryTrait;

beforeEach(function (): void {
    Schema::dropIfExists('data_table_query_trait_models');

    Schema::create('data_table_query_trait_models', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    config()->set('data-table-query-builder.query_params', [
        'take' => 'limit',
        'default_take' => 1,
        'skip' => 'offset',
        'search' => 'q',
        'sort' => 'order_by',
        'direction' => 'order_direction',
    ]);

    config()->set('data-table-query-builder.response_keys', [
        'data' => 'items',
        'count' => 'total',
    ]);

    request()->replace([
        'offset' => 0,
        'q' => 'Alpha',
        'order_by' => 'name',
        'order_direction' => 'asc',
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('data_table_query_trait_models');
});

it('uses configurable query params and response keys', function () {
    $model = new class extends Model
    {
        use DataTableQueryTrait;

        protected $table = 'data_table_query_trait_models';

        public $timestamps = false;

        protected $guarded = [];

        protected function getDataTableColumns(): array
        {
            return [
                'name' => [
                    'searchable' => true,
                ],
            ];
        }

        protected function getDataTableRelationships(): array
        {
            return [];
        }
    };

    $model->newQuery()->create([
        'name' => 'Alpha',
    ]);

    $model->newQuery()->create([
        'name' => 'Beta',
    ]);

    $result = $model->newQuery()->dataTableQuery();

    expect($result)->toHaveKeys(['items', 'total']);
    expect($result['items'])->toHaveCount(1);
    expect($result['total'])->toBe(1);
    expect($result['items']->first()->name)->toBe('Alpha');
});
