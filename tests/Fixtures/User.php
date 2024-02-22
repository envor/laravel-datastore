<?php

namespace Envor\Datastore\Tests\Fixtures;

use Envor\Datastore\Concerns\HasDatastores;
use Envor\Datastore\Contracts\ConfiguresDatastore;
use Envor\Datastore\Contracts\HasDatastoreContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasDatastoreContext
{
    use HasDatastores;
    use HasFactory;

    protected $guarded = [];

    public function datastoreContext(): ?ConfiguresDatastore
    {
        return $this->currentTeam;
    }

    /**
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentTeam()
    {

        return $this->belongsTo(Team::class, 'current_team_id');
    }
}
