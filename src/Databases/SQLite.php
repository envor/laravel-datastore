<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class SQLite extends Datastore
{

    public static function withPrefix(string $name, string $prefix): static
    {

        $directory = dirname($name) . DIRECTORY_SEPARATOR . $prefix;

        $instance = static::make($directory . DIRECTORY_SEPARATOR . basename($name));

        $instance->prefixed = true;

        return $instance;
    }

    protected static function makeAdminConfig(Datastore $datastore): array
    {
        $config = config('database.connections.sqlite');

        $config['name'] = $datastore->adminConnection;

        return $config;
    }

    protected static function makeName(string $name): string
    {
        if(str()->of($name)->contains(':memory:')) {
            return ':memory:';
        }

        return implode(DIRECTORY_SEPARATOR, [
            (string) str()->of(dirname($name)),
            (string) str()->of(basename($name))->finish('.sqlite'),
        ]);
    }
}
