<?php

namespace Envor\Datastore;

class DatastoreContext implements Contracts\HasDatastoreContext
{
    public function datastoreContext(): ?Contracts\ConfiguresDatastore
    {

        if (! auth()->check() && ! request()->user()) {
            return null;
        }

        $auth = auth()->user() ?? request()->user();

        if (! method_exists($auth, 'datastoreContext')) {
            return null;
        }

        return $auth->datastoreContext();
    }
}
