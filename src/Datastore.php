<?php

namespace Envor\Datastore;

use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;
use Illuminate\Database\Eloquent\Model;

class Datastore extends Model
{
    use HasPlatformUuids;
    use UsesPlatformConnection;

    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo();
    }
}
