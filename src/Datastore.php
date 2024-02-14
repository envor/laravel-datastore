<?php

namespace Envor\Datastore;

use Illuminate\Database\Eloquent\Model;

class Datastore extends Model
{
    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo();
    }
}
