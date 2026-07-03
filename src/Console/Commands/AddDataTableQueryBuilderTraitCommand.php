<?php

namespace Shoboske\DataTableQueryBuilder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class AddDataTableQueryBuilderTraitCommand extends Command
{
    protected $signature = 'datatable:add-trait {model? : The model class to update}';

    protected $description = 'Add the DataTableQueryBuilder trait and generated datatable methods to a model';

    public function handle(): int
    {
        $modelClass = $this->resolveModelClass();

        if ($modelClass === null) {
            return self::FAILURE;
        }

        if (! class_exists($modelClass)) {
            $this->error("Model class [$modelClass] could not be found.");

            return self::FAILURE;
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            $this->error("Class [$modelClass] is not an Eloquent model.");

            return self::FAILURE;
        }

        $reflectionClass = new ReflectionClass($modelClass);
        $filePath = $reflectionClass->getFileName();

        if (! is_string($filePath) || $filePath === '') {
            $this->error("Unable to determine the file for [$modelClass].");

            return self::FAILURE;
        }

        $originalContents = file_get_contents($filePath);

        if ($originalContents === false) {
            $this->error("Unable to read [$filePath].");

            return self::FAILURE;
        }

        $model = new $modelClass;
        $columns = $this->resolveColumns($model);

        $updatedContents = $this->ensureTraitImport($originalContents);
        $updatedContents = $this->ensureTraitUsage($updatedContents);
        $updatedContents = $this->ensureGeneratedMethods($updatedContents, $columns);

        if ($updatedContents === $originalContents) {
            $this->info("No changes were needed for [$modelClass].");

            return self::SUCCESS;
        }

        file_put_contents($filePath, $updatedContents);

        $this->info("Updated [$modelClass] with the DataTableQueryBuilder trait.");

        return self::SUCCESS;
    }

    protected function resolveModelClass(): ?string
    {
        $modelClass = $this->argument('model');

        if (! is_string($modelClass) || trim($modelClass) === '') {
            $modelClass = $this->ask('Which model class should be updated?');
        }

        if (! is_string($modelClass) || trim($modelClass) === '') {
            $this->error('A model class is required.');

            return null;
        }

        $modelClass = ltrim(trim($modelClass), '\\');

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        if (! str_contains($modelClass, '\\')) {
            $applicationNamespace = app()->getNamespace();
            $conventionalNamespaces = [
                $applicationNamespace.'Models\\'.$modelClass,
                $applicationNamespace.$modelClass,
            ];

            foreach ($conventionalNamespaces as $conventionalNamespace) {
                if (class_exists($conventionalNamespace)) {
                    return $conventionalNamespace;
                }
            }
        }

        $this->error("Model class [$modelClass] could not be resolved.");

        return null;
    }

    protected function resolveColumns(Model $model): array
    {
        $fillableColumns = array_values(array_filter(
            $model->getFillable(),
            static fn (mixed $column): bool => is_string($column) && trim($column) !== ''
        ));

        if ($fillableColumns !== []) {
            return $fillableColumns;
        }

        $connectionName = $model->getConnectionName();
        $schema = $connectionName !== null ? Schema::connection($connectionName) : Schema::connection(config('database.default'));

        return array_values(array_filter(
            $schema->getColumnListing($model->getTable()),
            static fn (mixed $column): bool => is_string($column) && trim($column) !== ''
        ));
    }

    protected function ensureTraitImport(string $contents): string
    {
        $import = 'use Shoboske\\DataTableQueryBuilder\\Traits\\DataTableQueryBuilderTrait;';

        if (str_contains($contents, $import)) {
            return $contents;
        }

        $useStatementPattern = '/^use\s+[^;]+;\s*$/m';

        if (preg_match_all($useStatementPattern, $contents, $matches, PREG_OFFSET_CAPTURE) > 0) {
            $lastMatch = $matches[0][\count($matches[0]) - 1];
            $insertionPosition = $lastMatch[1] + strlen($lastMatch[0]);

            return substr($contents, 0, $insertionPosition)."\n".$import.substr($contents, $insertionPosition);
        }

        if (preg_match('/^namespace\s+[^;]+;\s*$/m', $contents, $match, PREG_OFFSET_CAPTURE)) {
            $insertionPosition = $match[0][1] + strlen($match[0][0]);

            return substr($contents, 0, $insertionPosition)."\n\n".$import.substr($contents, $insertionPosition);
        }

        return $import."\n\n".$contents;
    }

    protected function ensureTraitUsage(string $contents): string
    {
        if (preg_match('/^\s*use\s+DataTableQueryBuilderTrait;\s*$/m', $contents) === 1) {
            return $contents;
        }

        if (! preg_match('/class\s+[^{]+\{/', $contents, $match, PREG_OFFSET_CAPTURE)) {
            return $contents;
        }

        $insertionPosition = $match[0][1] + strlen($match[0][0]);

        return substr($contents, 0, $insertionPosition)."\n    use DataTableQueryBuilderTrait;".substr($contents, $insertionPosition);
    }

    protected function ensureGeneratedMethods(string $contents, array $columns): string
    {
        $methods = [];

        if (! $this->containsMethod($contents, 'getDataTableColumns')) {
            $methods[] = $this->generateColumnsMethod($columns);
        }

        if (! $this->containsMethod($contents, 'getDataTableRelationships')) {
            $methods[] = $this->generateRelationshipsMethod();
        }

        if ($methods === []) {
            return $contents;
        }

        $methodBlock = implode("\n\n", $methods);

        return preg_replace('/\n}\s*$/', "\n\n".$methodBlock."\n}", $contents, 1) ?? $contents;
    }

    protected function containsMethod(string $contents, string $methodName): bool
    {
        return preg_match('/^(?:\s*)(?:public|protected|private)\s+function\s+'.preg_quote($methodName, '/').'\s*\(/m', $contents) === 1;
    }

    protected function generateColumnsMethod(array $columns): string
    {
        $columnsBlock = [];

        foreach ($columns as $column) {
            $column = (string) $column;

            $columnsBlock[] = "        '{$column}' => [";
            $columnsBlock[] = "            config('data-table-query-builder.models.search_term') => true,";
            $columnsBlock[] = '        ],';
        }

        $columnsBlock = implode("\n", $columnsBlock);

        if ($columnsBlock !== '') {
            $columnsBlock .= "\n";
        }

        return <<<PHP
    protected function getDataTableColumns(): array
    {
        return [
{$columnsBlock}        ];
    }
PHP;
    }

    protected function generateRelationshipsMethod(): string
    {
        return <<<'PHP'
    protected function getDataTableRelationships(): array
    {
        return [];
    }
PHP;
    }
}
