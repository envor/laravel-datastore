<?php

// config for Envor/Datastore
return [
    'model' => \Envor\Datastore\Models\Datastore::class,
    'create_databases' => env('DATASTORE_CREATE_DATABASES', true),
    'push_middleware' => env('DATASTORE_PUSH_CONTEXT_MIDDLEWARE', false),
    'autoconfigure_default_context' => env('AUTOCONFIGURE_DEFAULT_CONTEXT', false),
];
