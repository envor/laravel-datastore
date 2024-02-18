<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Models\Datastore;

trait HasDatastores
{
    public function datastores()
    {
        return $this->morphMany(Datastore::class, 'owner');
    }
}
