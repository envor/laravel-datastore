<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MySql extends Datastore
{
    protected function makeAdminConfig() : mixed
    {
        return config('database.connections.mysql');
    }
}
