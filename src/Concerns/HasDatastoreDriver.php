<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Datastore;
use Envor\Datastore\Driver;

trait HasDatastoreDriver
{
    public const DEFAULT_DRIVER = Driver::SQLite;

    public function owner()
    {
        return $this->morphTo();
    }

    protected static function bootHasDatastoreDriver()
    {
        static::creating(function (self $model) {
            if (! $model->driver) {
                $model->driver = $model::DEFAULT_DRIVER;
                // $model->save();
            }
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
        if ($this->database()->exists()) {
            $this->name = $this->name.'_1';

            return $this->createDatabase();
        }

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

    public function database(): ?Datastore
    {
        return $this->driver->toNewDatabase($this->name);
    }
}
