<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MariaDB extends Datastore
{
    protected function makeAdminConfig(): array
    {
        $database = config('database.connections.mariadb');

        $database['name'] = $this->adminName;

        return $database;
    }
}
