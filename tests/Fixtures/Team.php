<?php

namespace Envor\Datastore\Tests\Fixtures;

use Envor\Datastore\Concerns\BelongsToDatastore;
use Envor\Datastore\Contracts\ConfiguresDatastore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model implements ConfiguresDatastore
{
    use BelongsToDatastore;
    use HasFactory;

    /**
     * Get the owner of the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $guarded = [];
}
