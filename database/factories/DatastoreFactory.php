<?php

namespace Envor\Datastore\Database\Factories;

use Envor\Datastore\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatastoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Envor\Datastore\Tests\Fixtures\Datastore::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => str()->ulid(),
            'name' => ':memory:'.str()->ulid(),
            'driver' => Driver::SQLite,
        ];
    }
}
