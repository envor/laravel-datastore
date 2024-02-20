<?php

namespace Envor\Datastore\Contracts;

interface HasDatastoreContext
{
    public function datastoreContext() : ?ConfiguresDatastore;
}