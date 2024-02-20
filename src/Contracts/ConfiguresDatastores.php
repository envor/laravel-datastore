<?php

namespace Envor\Datastore\Contracts;

use Envor\Datastore\Datastore;

interface ConfiguresDatastores
{
    public function configure();

    public function use();

    public function database() : ?Datastore;
}