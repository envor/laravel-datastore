<?php

namespace Envor\Datastore;

enum Driver: string
{
    case SQLite = 'sqlite';
    case MariaDB = 'mariadb';
    case MySQL = 'mysql';

    public function toNewDatabase($name): Datastore
    {
        return DatabaseFactory::createForDriver($name, $this->value);
    }
}
