<?php

namespace BWICompanies\DB2Driver\Schema;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class DB2Builder extends Builder
{
    /**
     * Determine if the given table exists.
     */
    public function hasTable($table): bool
    {
        $sql = $this->grammar->compileTableExists();
        $schemaTable = explode('.', $table);

        if (count($schemaTable) > 1) {
            $schema = $schemaTable[0];
            $table = $this->connection->getTablePrefix().$schemaTable[1];
        } else {
            $schema = $this->connection->getDefaultSchema();
            $table = $this->connection->getTablePrefix().$table;
        }

        return count($this->connection->select($sql, [
            $schema,
            $table,
        ])) > 0;
    }

    /**
     * Get the column listing for a given table.
     */
    public function getColumnListing($table): array
    {
        $sql = $this->grammar->compileColumnExists();
        $database = $this->connection->getDatabaseName();
        $table = $this->connection->getTablePrefix().$table;

        $tableExploded = explode('.', $table);

        if (count($tableExploded) > 1) {
            $database = $tableExploded[0];
            $table = $tableExploded[1];
        }

        $results = $this->connection->select($sql, [
            $database,
            $table,
        ]);

        $res = $this->connection->getPostProcessor()
                                ->processColumnListing($results);

        return array_values(array_map(function ($r) {
            return $r->column_name;
        }, $res));
    }

    /**
     * Execute the blueprint to build / modify the table.
     */
    protected function build(Blueprint $blueprint)
    {
        $schemaTable = explode('.', $blueprint->getTable());

        if (count($schemaTable) > 1) {
            $this->connection->setCurrentSchema($schemaTable[0]);
        }

        $blueprint->build($this->connection, $this->grammar);
        $this->connection->resetCurrentSchema();
    }

    /**
     * Create a new command set with a Closure.
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        }

        return new DB2Blueprint($table, $callback);
    }
}
