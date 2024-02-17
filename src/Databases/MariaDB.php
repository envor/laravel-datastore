<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MariaDB extends Datastore
{
    protected static function makeAdminConfig(Datastore $datastore): array
    {
        $config = config('database.connections.mariadb');

        $config['name'] = $datastore->adminConnection;

        return $config;
    }
}
