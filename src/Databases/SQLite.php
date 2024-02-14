<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class SQLite extends Datastore
{

    protected function makeAdminConfig()
    {
        return config('database.connections.sqlite');
    }

    protected function makeName(string $name)
    {
        return implode(DIRECTORY_SEPARATOR,[
            (string) str()->of(dirname($name))->finish(DIRECTORY_SEPARATOR . 'datastore' . DIRECTORY_SEPARATOR),
            (string) str()->of(basename($name))->finish('.sqlite'),
        ]);
    }

    protected function configureDatabase()
    {
        $connection = basename($this->name, '.sqlite');

        config([
            'database.default' => $connection,
            "database.connections.{$connection}" => $this->config,
        ]);
    }
}
