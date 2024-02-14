<?php

namespace Envor\Datastore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Envor\Datastore\Datastore
 */
class Datastore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Envor\Datastore\Datastore::class;
    }
}
