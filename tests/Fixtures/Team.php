<?php

namespace Envor\Datastore\Tests\Fixtures;

use Envor\Datastore\Concerns\BelongsToDatastore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use BelongsToDatastore;
    use HasFactory;

    protected $guarded = [];
}
