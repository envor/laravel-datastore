<?php

use Envor\Datastore\Models\Datastore;
use Illuminate\Support\Facades\Schema;

it('will configure the datastore', function () {
    $datastore = Datastore::factory()->create();

    $datastore->configure();

    expect(config('database.default'))->toBe($datastore->database()->name);

    expect(config("database.connections.{$datastore->database()->name}"))->toBe($datastore->database()->config);
});

it('will create and migrate the datastore', function () {
    $datastore = Datastore::factory()->create();

    $datastore->migrate();

    expect(Schema::connection($datastore->database()->name)->hasTable('migrations'))->toBeTrue();
});