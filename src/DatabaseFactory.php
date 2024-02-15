<?php

namespace Envor\Datastore;

use Illuminate\Contracts\Filesystem\Factory;

class DatabaseFactory
{
    public static function newDatabase(string $name, string $driver, $disk = 'local')
    {
        $datastore = match ($driver) {
            'sqlite' => static::newSqliteDatabase($name, $disk),
            'mariadb' => new Databases\MariaDB($name),
            'mysql' => new Databases\MySql($name),
            default => throw new \Exception("Driver {$driver} not supported"),
        };

        return $datastore;
    }

    protected static function newSqliteDatabase(string $name, $disk = 'local')
    {
        if ($name === ':memory:') {
            return new Databases\SQLite($name);
        }

        $disk = app(Factory::class)->disk($disk);

        $path = $disk->path($name);

        return new Databases\SQLite($path);
    }
}
