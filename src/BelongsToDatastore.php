<?php

namespace Envor\Datastore;

trait BelongsToDatastore
{

    public static function bootBelongsToDatastore()
    {
        static::creating(function ($model) {
            if(! $model->datastore_id) {
                $model->datastore_id = Datastore::create([
                    'name' => (string) str()->of($model->name)->slug('_'),
                ])->id;
            }
        });
    }

    public function datastore()
    {
        return $this->belongsTo(Datastore::class);
    }
}