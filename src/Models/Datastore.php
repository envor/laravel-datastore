<?php

namespace Envor\Datastore\Models;

use Envor\Datastore\Concerns\HasDatastoreDriver;
use Envor\Datastore\Driver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datastore extends Model
{
    use HasDatastoreDriver;
    use HasFactory;

    public const DEFAULT_DRIVER = Driver::SQLite;

    protected $guarded = [];

    protected $casts = [
        'driver' => Driver::class,
    ];
}
