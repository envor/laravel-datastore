<?php

namespace Envor\Datastore\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToDatastore
{
    public ?string $datastore_driver = null;

    public static function bootBelongsToDatastore()
    {
        static::creating(function ($model) {
            if (! $model->datastore_id) {
                $model->datastore_id = $model->createDatastore()->id;
            }
        });
    }

    protected function createDatastore(): Model
    {
        $model = config('datastore.model');
        $auth = config('auth.providers.users.model');

        $attributes = [
            'name' => (string) str()->of($this->name)->slug('_'),
            'driver' => $this->datastore_driver ?? $model::DEFAULT_DRIVER,
        ];

        if ($this->user_id) {
            $attributes['owner_type'] = $auth;
            $attributes['owner_id'] = $this->user_id;
        }

        // dd($attributes, $model::create($attributes));

        return $model::create($attributes);
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
        //

        return $this;
    }

    protected function configured()
    {
        //

        return $this;
    }

    protected function using()
    {
        //

        return $this;
    }

    protected function configuring()
    {
        //

        return $this;
    }
}
