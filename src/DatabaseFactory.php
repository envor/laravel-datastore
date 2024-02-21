<?php

namespace Envor\Datastore;

use Illuminate\Contracts\Filesystem\Factory;

class DatabaseFactory
{
    public static function cleanupRepository(): void
    {
        $frameworkConnections = require base_path('vendor/laravel/framework/config/database.php'); ['connections'];
        $appConnections =  file_exists(config_path('database.php')) ? require config_path('database.php') : [];

        config(['database.connections' => array_merge($frameworkConnections, $appConnections)]);
    }

    /**
     * Create a new database instance with the given name and driver.
     */
    public static function newDatabase(string $name, string $driver, $disk = 'local'): Datastore
    {
        // static::cleanupRepository();

        return match ($driver) {
            'sqlite' => static::prefixedSqlite($name, $disk),
            'mariadb' => Databases\MariaDB::withPrefix($name, 'datastore'),
            'mysql' => Databases\MySql::withPrefix($name, 'datastore'),
            default => throw new \Exception("Driver {$driver} not supported"),
        };
    }

    protected static function prefixedSqlite(string $name, $disk = 'local'): Databases\SQLite
    {
        if ($name === ':memory:') {
            return Databases\SQLite::make($name);
        }

        $disk = app(Factory::class)->disk($disk);

        $path = $disk->path($name);

        return Databases\SQLite::withPrefix($path, 'datastore');
    }
}
