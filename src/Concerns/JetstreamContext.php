<?php

namespace Envor\Datastore\Concerns;

use Envor\Datastore\Contracts\ConfiguresDatastore;

trait JetstreamContext
{
    public function datastoreContext() : ?ConfiguresDatastore
    {
        return $this->currentTeam;
    }
}