<?php

namespace BWICompanies\DB2Driver;

use BWICompanies\DB2Driver\Schema\DB2Builder;
use BWICompanies\DB2Driver\Schema\DB2SchemaGrammar;
use Illuminate\Database\Connection;
use PDO;

class DB2Connection extends Connection
{
    /**
     * The name of the default schema.
     */
    protected $defaultSchema;

    /**
     * The name of the current schema in use.
     */
    protected $currentSchema;

    public function __construct(
        PDO $pdo,
        string $database = '',
        string $tablePrefix = '',
        array $config = []
        ) {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $this->currentSchema = $this->defaultSchema = strtoupper($config['schema'] ?? null);
    }

    /**
     * Get the name of the default schema.
     */
    public function getDefaultSchema(): string
    {
        return $this->defaultSchema;
    }

    /**
     * Reset to default the current schema.
     */
    public function resetCurrentSchema()
    {
        $this->setCurrentSchema($this->getDefaultSchema());
    }

    /**
     * Set the name of the current schema.
     */
    public function setCurrentSchema(string $schema)
    {
        $this->statement('SET SCHEMA ?', [strtoupper($schema)]);
    }

    /**
     * Execute a system command on IBMi.
     */
    public function executeCommand($command)
    {
        $this->statement('CALL QSYS2.QCMDEXC(?)', [$command]);
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new DB2Builder($this);
    }

    /**
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        $defaultGrammar = new DB2QueryGrammar;

        // If a date format was specified in constructor
        if (array_key_exists('date_format', $this->config)) {
            $defaultGrammar->setDateFormat($this->config['date_format']);
        }

        // If offset compatability mode was specified in constructor
        if (array_key_exists('offset_compatibility_mode', $this->config)) {
            $defaultGrammar->setOffsetCompatibilityMode($this->config['offset_compatibility_mode']);
        }

        return $this->withTablePrefix($defaultGrammar);
    }

    /**
     * Get the efault grammar for specified Schema
     */
    protected function getDefaultSchemaGrammar(): \Illuminate\Database\Grammar
    {
        return new DB2SchemaGrammar;
    }

    /**
     * Get the default post processor instance
     */
    protected function getDefaultPostProcessor(): \Illuminate\Database\Query\Processors\Processor
    {
        return new DB2Processor;
    }
}
