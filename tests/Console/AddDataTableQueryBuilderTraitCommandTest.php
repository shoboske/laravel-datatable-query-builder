<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shoboske\DataTableQueryBuilder\Tests\Models\CommandExistingTraitModel;
use Shoboske\DataTableQueryBuilder\Tests\Models\CommandFillableModel;
use Shoboske\DataTableQueryBuilder\Tests\Models\CommandSchemaFallbackModel;

function restoreModelFile(string $filePath, string $originalContents, callable $callback): void
{
    try {
        $callback();
    } finally {
        file_put_contents($filePath, $originalContents);
    }
}

it('adds the trait and columns method from fillable attributes', function () {
    $filePath = (new ReflectionClass(CommandFillableModel::class))->getFileName();
    expect($filePath)->toBeString();

    $originalContents = file_get_contents($filePath);
    expect($originalContents)->toBeString();

    restoreModelFile($filePath, $originalContents, function () use ($filePath) {
        $this->artisan('data-table:add-trait', [
            'model' => CommandFillableModel::class,
        ])->assertExitCode(0);

        $updatedContents = file_get_contents($filePath);

        expect($updatedContents)->toContain('use Shoboske\\DataTableQueryBuilder\\Traits\\DataTableQueryBuilderTrait;');
        expect($updatedContents)->toContain('use DataTableQueryBuilderTrait;');
        expect($updatedContents)->toContain('protected function getDataTableColumns(): array');
        expect($updatedContents)->toContain("'name' => [");
        expect($updatedContents)->toContain("'email' => [");
        expect($updatedContents)->toContain("config('data-table-query-builder.models.search_term') => true,");
        expect($updatedContents)->toContain('protected function getDataTableRelationships(): array');
    });
});

it('falls back to schema columns when fillable is empty', function () {
    $tableName = 'command_schema_fallback_models';

    Schema::dropIfExists($tableName);
    Schema::create($tableName, function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->text('body');
        $table->timestamps();
    });

    $filePath = (new ReflectionClass(CommandSchemaFallbackModel::class))->getFileName();
    expect($filePath)->toBeString();

    $originalContents = file_get_contents($filePath);
    expect($originalContents)->toBeString();

    restoreModelFile($filePath, $originalContents, function () use ($filePath) {
        $this->artisan('data-table:add-trait', [
            'model' => CommandSchemaFallbackModel::class,
        ])->assertExitCode(0);

        $updatedContents = file_get_contents($filePath);

        expect($updatedContents)->toContain("'id' => [");
        expect($updatedContents)->toContain("'title' => [");
        expect($updatedContents)->toContain("'body' => [");
        expect($updatedContents)->toContain("'created_at' => [");
        expect($updatedContents)->toContain("'updated_at' => [");
    });

    Schema::dropIfExists($tableName);
});

it('does not duplicate the trait or methods when run twice', function () {
    $filePath = (new ReflectionClass(CommandExistingTraitModel::class))->getFileName();
    expect($filePath)->toBeString();

    $originalContents = file_get_contents($filePath);
    expect($originalContents)->toBeString();

    restoreModelFile($filePath, $originalContents, function () use ($filePath) {
        $this->artisan('data-table:add-trait', [
            'model' => CommandExistingTraitModel::class,
        ])->assertExitCode(0);

        $this->artisan('data-table:add-trait', [
            'model' => CommandExistingTraitModel::class,
        ])->assertExitCode(0);

        $updatedContents = file_get_contents($filePath);

        expect(substr_count($updatedContents, 'use Shoboske\\DataTableQueryBuilder\\Traits\\DataTableQueryBuilderTrait;'))->toBe(1);
        expect(substr_count($updatedContents, 'use DataTableQueryBuilderTrait;'))->toBe(1);
        expect(substr_count($updatedContents, 'protected function getDataTableColumns(): array'))->toBe(1);
        expect(substr_count($updatedContents, 'protected function getDataTableRelationships(): array'))->toBe(1);
    });
});
