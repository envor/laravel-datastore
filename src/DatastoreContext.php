<?php

namespace Envor\Datastore;

class DatastoreContext implements Contracts\HasDatastoreContext
{
    public function datastoreContext(): ?Contracts\ConfiguresDatastore
    {
        $auth = null;

        if (auth()->check() || request()->user()) {
            $auth = auth()->user() ?? request()->user();
        }

        if ($auth && method_exists($auth, 'datastoreContext')) {
           return $auth->datastoreContext();
        }

        if (isset(app()['datastore_context'])) {
            return app()['datastore_context'];
        }

        return null;
    }
}
