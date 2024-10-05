<?php

use Envor\Datastore\Datastore;
use Envor\Datastore\Tests\Fixtures\User;
use Livewire\Volt\Volt;

beforeEach(function (): void {

    teamsAndUsersSchema();

    $user = User::factory()->create();

    $this->team = \Envor\Datastore\Tests\Fixtures\Team::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->datastore = \Envor\Datastore\Models\Datastore::factory()->create([
        'owner_id' => $user->id,
        'owner_type' => User::class,
    ]);

    $user->update([
        'current_team_id' => $this->team->id,
    ]);

    $this->actingAs($user);
});

it('can update a models datastore', function (): void {
    $this->team->update([
        'datastore_id' => $this->datastore->id,
    ]);

    expect($this->team->datastore->id)->toBe($this->datastore->id);
});

it('can update a models datastore using livewire volt', function (): void {

    Datastore::fake();

    Volt::test('update-belongs-to-datastore-form', [
        'model' => $this->team,
    ])->set('data.datastore_uuid', (string) $this->datastore->uuid)->call('updateModelDatastore');

    expect($this->team->fresh()->datastore_id)->toBe($this->datastore->id);
});
