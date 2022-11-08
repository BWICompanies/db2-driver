<?php

namespace BWICompanies\DB2Driver;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DB2DriverServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // so this works now but.... i shouldn't have to do this right?
        $package->name('db2-driver')
            ->publishesServiceProvider('DB2ServiceProvider');
    }

    // public function register()
    // {
        /** #############################################################################################################
         *
         * cooperl22/laravel-db2 extends the database connection of the database connection you define in database.php.
         *
         * It does this by searching the database.php for a connection identified with a predefined string (db2_ibmi_odbc).
         * This way, only that connection will be overridden. It's done like so:
         *
         * $this->app['db']->extend('db2_ibmi_odbc', DB2Connection());
         *
         * I might need to do it this way to prevent myself from overriding all DB connections with what I planned
         * to use. Which is :
         *
         * // In AppServiceProvider@register()
         * // Replace 'mysql' with whatever the connection name is...?
         * Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
         *      return new DB2Connection();
         * });
         *
         * We shall see.
         *
         * ############################################################################################################# */

        // $connector = new DB2Connector();

        // literally an instantiated PDO with the connection string in it.
        // $db2Connection = $connector->connect($config);

        // Conditionally return this. What's the condition, though. Does extending DB like this make all connections (sql server, too)
        // try to utilize this??
        // return new DB2Connection($db2Connection, $config['database'], $config['prefix'], $config);
    //     return true;
    // }
}
