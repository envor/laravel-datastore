<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Models\Datastore;
use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;

trait HasDatastores
{
    use HasPlatformUuids;
    use UsesPlatformConnection;

    public function datastores()
    {
        return $this->morphMany(Datastore::class, 'owner');
    }
}
