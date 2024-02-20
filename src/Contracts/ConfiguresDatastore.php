<?php

namespace Envor\Datastore\Contracts;

use Envor\Datastore\Datastore;

interface ConfiguresDatastore
{
    public function configure();

    public function use();

    public function database() : ?Datastore;
}