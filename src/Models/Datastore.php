<?php

namespace Envor\Datastore\Models;

use Envor\Datastore\Concerns\HasDatastoreDriver;
use Envor\Datastore\Contracts\ConfiguresDatastore;
use Envor\Datastore\Driver;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datastore extends Model implements ConfiguresDatastore
{
    use HasDatastoreDriver;
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'driver' => Driver::class,
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function getConnectionName(): string
    {
        return config('database.platform');
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
