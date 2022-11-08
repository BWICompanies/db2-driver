<?php

namespace BWICompanies\DB2Driver;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class DB2ServiceProvider extends ServiceProvider
{
    public function register()
    {
        /**
         * Override any database connections using the 'db2' driver.
         */
        Connection::resolverFor('db2', function ($connection, $database, $prefix, $config) {
            $connector = new DB2Connector();

            $connection = new DB2Connection(
                $connector->connect($config),
                $database,
                $prefix,
                $config
            );

            return $connection;
        });
    }
}
