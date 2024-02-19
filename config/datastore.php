<?php

// config for Envor/Datastore
return [
    'model' => \Envor\Datastore\Models\Datastore::class,
    'create_databases' => env('DATASTORE_CREATE_DATABASES', true),
];
