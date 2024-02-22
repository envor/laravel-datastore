<?php

use Envor\Datastore\Models\Datastore;
use Illuminate\Support\Facades\Auth;

use function Livewire\Volt\{uses, computed, state, mount};

state([
    'data' => [],
    'model' => null,
    'modelName' => 'Team',
]);

mount(
    function ($model) {
        $this->model = $model;

        $this->state = [
            'datastore_uuid' => $model->datastore->uuid,
        ];
    }
);

$user = computed(fn() => Auth::user());

$datastores = computed(fn() => $this->model->owner->datastores);

$updateModelDatastore = function () {

    $this->resetErrorBag();

    $this->validate([
        'data.datastore_uuid' => ['required','exists:'.config('database.platform').'.datastores,uuid'],
    ]);

    $datastore = Datastore::where('uuid', $this->data['datastore_uuid'])->firstOrFail();

    $this->model->forceFill([
        'datastore_id' => $datastore->id,
    ])->save();

    $this->model->migrate()->configure()->use();

    $this->dispatch('saved');
};?>

<x-platform::form-section submit="updateModelDatastore">
    <x-slot name="title">
        {{ __('Datastore') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The :modelName\'s database. You may select any one of your databases here and change it back at any time.', ['modelName' => $this->modelName]) }}
    </x-slot>

    <x-slot name="form">

        <div class="col-span-6 sm:col-span-4">
            <x-platform::label for="name" value="{{ __('Database') }}" />

            <x-platform::select id="name" type="text" class="block w-full mt-1" wire:model="data.datastore_uuid" autofocus>
                @foreach ($this->datastores as $datastore)
                <option value="{{ $datastore->uuid }}">{{ $datastore->name }}</option>
                @endforeach
            </x-platform::select>

            <x-platform::input-error for="data.datastore_uuid" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        <x-platform::action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-platform::action-message>

        <x-platform::button>
            {{ __('Save') }}
        </x-platform::button>
    </x-slot>
</x-platform::form-section>

        
        
