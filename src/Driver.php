<?php

namespace Envor\Datastore;

enum Driver: string
{
    case SQLite = 'sqlite';
    case MariaDB = 'mariadb';
    case MySQL = 'mysql';

    /**
     * Create a new database instance with the given name.
     */
    public function toNewDatabase($name): Datastore
    {
        $disk = config()->has('filesystems.disks.datastores') ? 'datastores' : 'local';

        return DatabaseFactory::newDatabase($name, $this->value, $disk);
    }
}
