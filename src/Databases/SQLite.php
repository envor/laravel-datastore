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

    protected function makeAdminName(string $name) : string
    {
        return 'admin_'.basename($this->name, '.sqlite');
    }

    protected function makeAdminConfig() : array
    {
        $config = config('database.connections.sqlite');

        $config['name'] = $this->adminName;

        return $config;
    }

    protected function makeName(string $name) : string
    {

        if ($name === ':memory:') {
            return $name;
        }

        return implode(DIRECTORY_SEPARATOR, [
            (string) str()->of(dirname($name))->finish(DIRECTORY_SEPARATOR.'datastore'),
            (string) str()->of(basename($name))->finish('.sqlite'),
        ]);
    }

    protected function configureDatabase() : void
    {
        $connection = $this->connectionName;

        config([
            "database.connections.{$connection}" => $this->config,
        ]);

        config([
            'database.default' => $connection,
        ]);
    }

    protected function makeConnectionName(string $name): string
    {
        return basename($this->makeName($name), '.sqlite');        
    }
}
