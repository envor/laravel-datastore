<?php

namespace Envor\Datastore\Databases;

use Envor\Datastore\Datastore;

class MySql extends Datastore
{
    protected function makeAdminConfig() : array
    {
        $config = config('database.connections.mysql');

        $config['name'] = $this->adminName;

        return $config;
    }
}
