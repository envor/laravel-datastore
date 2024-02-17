<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MySql extends Datastore
{
    protected static function makeAdminConfig(Datastore $datastore) : array
    {
        $config = config('database.connections.mysql');

        $config['name'] = $datastore->adminConnection;

        return $config;
    }
}
