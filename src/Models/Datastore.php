<?php

namespace Envor\Datastore\Models;

use Envor\Datastore\Concerns\HasDatastoreDriver;
use Envor\Datastore\Contracts\ConfiguresDatastore;
use Envor\Datastore\Driver;
use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datastore extends Model implements ConfiguresDatastore
{
    use HasDatastoreDriver;
    use HasFactory;
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
}
