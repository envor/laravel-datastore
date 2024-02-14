<?php

namespace Envor\Datastore;

use Illuminate\Contracts\Filesystem\Factory;

class DatabaseFactory
{
    public static function createForDriver(string $name, string $driver, $disk = 'local')
    {
        $datastore = match ($driver) {
            'sqlite' => static::forSqlite($name, $disk),
            'mariadb' => new Databases\MariaDB($name),
            'mysql' => new Databases\MySql($name),
            default => throw new \Exception("Driver {$driver} not supported"),
        };

        return $datastore;
    }

    protected static function forSqlite(string $name, $disk = 'local')
    {
        $disk = app(Factory::class)->disk($disk);

        $path = $disk->path($name);

        return new Databases\SQLite($path);
    }
}
