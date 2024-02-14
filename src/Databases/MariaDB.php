<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MariaDB extends Datastore
{
    protected function makeAdminConfig() : mixed
    {
        return config('database.connections.mariadb');
    }
}
