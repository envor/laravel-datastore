<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Models\Datastore;
use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;

trait BelongsToDatastore
{
    use HasPlatformUuids;
    use UsesPlatformConnection;

    public static function bootBelongsToDatastore()
    {
        static::creating(function ($model) {
            if (! $model->datastore_id) {
                $model->datastore_id = Datastore::create([
                    'name' => (string) str()->of($model->name)->slug('_'),
                ])->id;
            }
        });
    }

    public function datastore()
    {
        return $this->belongsTo(Datastore::class);
    }

    public function configure()
    {
        return $this->datastore?->configure();
    }

    public function use()
    {
        return $this->datastore?->use();
    }
}
