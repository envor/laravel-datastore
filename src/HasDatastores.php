<?php

namespace Envor\Datastore;

trait HasDatastores
{
    public function datastores()
    {
        return $this->morphMany(Datastore::class, 'owner');
    }
}