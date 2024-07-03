<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class PgSql extends Datastore
{
    protected static function makeAdminConfig(Datastore $datastore): array
    {
        $config = config('database.connections.pgsql');

        $config['name'] = $datastore->adminConnection;

        return $config;
    }
}
