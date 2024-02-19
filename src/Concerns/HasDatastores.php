<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Models\Datastore;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDatastores
{
    public function datastores(): MorphMany
    {
        return $this->morphMany(Datastore::class, 'owner');
    }
}
