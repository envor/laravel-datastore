<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class SQLite extends Datastore
{

    protected function createDatabase(): bool
    {
        if ($this->name === ':memory:') {
            return true;
        }

       return parent::createDatabase();
    }

    protected function makeAdminConfig() : mixed
    {
        return config('database.connections.sqlite');
    }

    protected function makeName(string $name) : string
    {

        if ($name === ':memory:') {
            return $name;
        }

        return implode(DIRECTORY_SEPARATOR, [
            (string) str()->of(dirname($name))->finish(DIRECTORY_SEPARATOR.'datastore'.DIRECTORY_SEPARATOR),
            (string) str()->of(basename($name))->finish('.sqlite'),
        ]);
    }

    protected function configureDatabase() : void
    {
        $connection = basename($this->name, '.sqlite');

        config([
            "database.connections.{$connection}" => $this->config,
        ]);

        config([
            'database.default' => $connection,
        ]);
    }
}
