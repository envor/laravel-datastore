<?php

namespace Envor\Datastore\Tests\Fixtures;

use Envor\Datastore\Concerns\HasDatastoreDriver;
use Envor\Datastore\Driver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datastore extends Model
{
    use HasDatastoreDriver;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'driver' => Driver::class,
    ];
}
