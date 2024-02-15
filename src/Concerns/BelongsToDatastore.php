<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Models\Datastore;
use Envor\Platform\Concerns\HasPlatformUuids;
use Envor\Platform\Concerns\UsesPlatformConnection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToDatastore
{
    use HasPlatformUuids;
    use UsesPlatformConnection;

    public ?string $datastore_driver = null;

    public static function bootBelongsToDatastore()
    {
        static::creating(function ($model) {
            if (! $model->datastore_id) {
                $model->datastore_id = $model->createDatastore()->id;
            }
        });
    }

    protected function createDatastore() : Datastore
    {
        return Datastore::create([
            'name' => (string) str()->of($this->name)->slug('_'),
            'driver' => $this->datastore_driver ?? Datastore::DEFAULT_DRIVER,
        ]);
    }

    public function datastore() : BelongsTo
    {
        return $this->belongsTo(Datastore::class);
    }

    public function migrate()
    {
        $this->datastore?->migrate();

        return $this;
    }

    public function configure()
    {
        $this->preconfigure();

        $this->datastore?->configure();

        return $this;
    }

    public function use()
    {
        $this->preuse();

        $this->datastore?->use();

        return $this;
    }

    protected function preuse()
    {
        //

        return $this;
    }

    protected function preconfigure()
    {
        //

        return $this;
    }
}
