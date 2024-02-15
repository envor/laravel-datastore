<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Driver;
use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;
use Illuminate\Database\Eloquent\Model;

trait HasDatastoreDriver
{
    use HasPlatformUuids;
    use UsesPlatformConnection;

    public function owner()
    {
        return $this->morphTo();
    }

    protected static function bootHasDatastoreDriver()
    {
        static::created(function (Model $model) {
            $model->driver = $model->driver ?? Driver::SQLite;

            $model->createDatabase()->migrate();
        });
    }

    public function use()
    {
        app()->forgetInstance('datastore');

        app()->instance('datastore', $this);
    }

    public function createDatabase()
    {
        $this->database()->create();

        return $this;
    }

    public function configure()
    {
        $this->database()->configure();

        return $this;
    }

    public function migrate()
    {
        $this->database()->migrate();

        return $this;
    }

    public function database()
    {
        return $this->driver->toNewDatabase($this->name);
    }
}
