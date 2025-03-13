<?php

namespace BWICompanies\DB2Driver\Schema;

use BWICompanies\DB2Driver\DB2Processor;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class DB2Builder extends Builder
{
    /**
     * The schema grammar instance.
     *
     * @var \BWICompanies\DB2Driver\Schema\DB2SchemaGrammar
     */
    protected $grammar;

    /**
     * The database connection instance.
     *
     * @var \BWICompanies\DB2Driver\DB2Connection
     */
    protected $connection;

    /**
     * Determine if the given table exists.
     *
     * @param string $table
     * @return bool
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
     *
     * @param string $table
     * @return array
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

        
        $postProcessor = $this->connection->getPostProcessor();

        // Ensure $postProcessor is a DB2Processor, since getPostProcessor() 
        // returns a general Processor type that lacks processColumnListing().
        if (!($postProcessor instanceof DB2Processor)) {
            throw new \Exception('Invalid post-processor: expected DB2Processor or a subclass.');
        }

        // Now it's safe to call processColumnListing().
        $res = $postProcessor->processColumnListing($results);

        return array_values(array_map(function ($r) {
            return $r->column_name;
        }, $res));
    }

     /**
     * Execute the blueprint to build / modify the table.
     *
     * @param Blueprint $blueprint
     * @return void
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
     *
     * @param string        $table
     * @param null|\Closure $callback
     * @return DB2Blueprint
     */
    protected function createBlueprint($table, ?Closure $callback = null)
    {
        // PHPStan warns that $this->resolver is always set because it's documented as a \Closure.
        // However, since this is only defined in a docblock (not enforced in PHP), we check it anyway.
        // Ignoring `isset.property` warning as a precaution.
        if (isset($this->resolver)) { // @phpstan-ignore isset.property
            return call_user_func($this->resolver, $table, $callback);
        }

        // Since PHPStan assumes the if-condition is always true, it marks this as unreachable code.
        // Ignoring `deadCode.unreachable` because we're explicitly handling a case that should never happen.
        return new DB2Blueprint($table, $callback); // @phpstan-ignore deadCode.unreachable 
    }
}
