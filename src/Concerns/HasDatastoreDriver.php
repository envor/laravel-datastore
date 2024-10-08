<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Datastore;
use Envor\Datastore\Driver;

trait HasDatastoreDriver
{
    public const DEFAULT_DRIVER = Driver::SQLite;

    protected static function bootHasDatastoreDriver()
    {
        static::creating(function (self $model): void {
            if (! $model->driver) {
                $model->driver = $model::DEFAULT_DRIVER;
            }
            $model->createDatabase()->migrate();
        });
    }

    public function use()
    {
        app()->forgetInstance('datastore');

        app()->instance('datastore', $this);

        return $this;
    }

    public function createDatabase()
    {
        $defaultConnection = config('database.default');

        if (app()->has('datastore_faking') || $this->name === config("database.connections.{$defaultConnection}.database")) {
            return $this;
        }

        if (count(explode('_1', $this->name)) > 10) {
            throw new \Exception('Database name is too long');
        }

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

        // dont do this yet
        // parent::clearBootedModels();

        return $this;
    }

    public function migrate()
    {
        $this->database()->migrate();

        return $this;
    }

    public function database(): ?Datastore
    {
        $database = $this->driver->toNewDatabase($this->name);

        if (isset($this->migration_path)) {
            $database->migratePath($this->migration_path);
        }

        return $database;
    }
}
