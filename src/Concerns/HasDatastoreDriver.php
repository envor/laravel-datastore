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

    protected $guarded = [];

    protected $casts = [
        'driver' => Driver::class,
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    protected static function bootHasDatastoreDriver()
    {
        static::created(function (Model $model) {
            $model->driver = $model->driver ?? Driver::SQLite;

            $model->createDatabase();
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
    }

    public function configure()
    {
        return $this->database()->configure();
    }

    protected function database()
    {
        return $this->driver->toNewDatabase($this->name);
    }
}
