<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Datastore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToDatastore
{
    public ?string $datastore_driver = null;

    public ?string $migration_path = null;

    public static function bootBelongsToDatastore()
    {
        static::creating(function ($model) {
            if (! $model->datastore_id) {
                $model->datastore_id = $model->createDatastore()->id;
            }
        });
    }

    public function getDatastoreDriver(): ?string
    {
        return $this->datastore_driver;
    }

    protected function createDatastore(): Model
    {
        $model = config('datastore.model');
        $auth = config('auth.providers.users.model');

        $attributes = [
            'name' => (string) str()->of($this->name)->slug('_'),
            'driver' => $this->getDatastoreDriver() ?? $model::DEFAULT_DRIVER,
        ];

        if (isset($this->user_id)) {
            $attributes['owner_type'] = $auth;
            $attributes['owner_id'] = $this->user_id;
        }

        if (isset($this->migration_path)) {
            $attributes['migration_path'] = $this->migration_path;
        }

        return $model::create($attributes);
    }

    public function database(): ?Datastore
    {
        return $this->datastore?->database();
    }

    public function datastore(): BelongsTo
    {
        $model = config('datastore.model');

        return $this->belongsTo($model, 'datastore_id', 'id');
    }

    public function migrate()
    {
        $this->datastore?->migrate();

        return $this;
    }

    public function configure()
    {
        $this->configuring();

        $this->datastore?->configure();

        $this->configured();

        return $this;
    }

    public function use()
    {
        $this->using();

        $this->datastore?->use();

        $this->used();

        return $this;
    }

    protected function used()
    {
        return $this;
    }

    protected function configured()
    {
        return $this;
    }

    protected function using()
    {
        return $this;
    }

    protected function configuring()
    {
        return $this;
    }
}
