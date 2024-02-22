<?php

use Envor\Datastore\Contracts\HasDatastoreContext;
use Envor\Datastore\Datastore;
use Envor\Datastore\DatastoreContext;
use Envor\Datastore\Models\Datastore as DatastoreModel;
use Envor\Datastore\Tests\Fixtures\Team;
use Envor\Datastore\Tests\Fixtures\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Datastore::configureDatastoreContextUsing(DatastoreContext::class);

    teamsAndUsersSchema();

    /** @var DatastoreModel $datastoreModel */
    $datastoreModel = config('datastore.model');

    $datastore = $datastoreModel::factory()->create();

    $team = Team::factory()->create([
        'datastore_id' => $datastore->id,
    ]);

    $this->team = $team;

    $this->datastore = $datastore;
});

it('can configure a datastore using context', function () {
    $user = User::factory()->create([
        'current_team_id' => $this->team->id,
    ]);

    $this->actingAs($user);

    $context = app(HasDatastoreContext::class)->datastoreContext();

    $context->configure();

    expect(config('database.default'))->toBe($this->datastore->database()->name);

    expect(config("database.connections.{$this->datastore->database()->name}"))->toBe($this->datastore->database()->config);
});

test('the middleware in isolation', function () {
    $user = User::factory()->create([
        'current_team_id' => $this->team->id,
    ]);

    $this->actingAs($user);

    $middleware = new \Envor\Datastore\DatastoreContextMiddleware();

    $response = $middleware->handle(
        createRequest('GET', '/'),
        fn () => new \Symfony\Component\HttpFoundation\Response('OK'),
    );

    expect(config('database.default'))->toBe($this->datastore->database()->name);
});

test('the middleware in full app', function () {

    $this->withoutExceptionHandling();

    $this->get('/datastore-context')->assertOk();

    $user = User::factory()->create([
        'current_team_id' => $this->team->id,
    ]);

    $this->actingAs($user);

    $response = $this->get('/datastore-context');

    expect(config('database.default'))->toBe($this->datastore->database()->name);
});
