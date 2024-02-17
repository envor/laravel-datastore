<?php

namespace Envor\Datastore;

use Illuminate\Contracts\Filesystem\Factory;

class DatabaseFactory
{
    public static function newDatabase(string $name, string $driver, $disk = 'local')
    {
        $datastore = match ($driver) {
            'sqlite' => static::prefixedSqlite($name, $disk),
            'mariadb' => Databases\MariaDB::withPrefix($name, 'datastore'),
            'mysql' => Databases\MySql::withPrefix($name, 'datastore'),
            default => throw new \Exception("Driver {$driver} not supported"),
        };

        return $datastore;
    }

    protected static function prefixedSqlite(string $name, $disk = 'local')
    {
        if($name === ':memory:') {
            return Databases\SQLite::make($name);
        }

        $disk = app(Factory::class)->disk($disk);

        $path = $disk->path($name);

        return Databases\SQLite::withPrefix($path, 'datastore');
    }
}
