<?php

namespace Envor\Datastore\Database\Factories;

use Envor\Datastore\Tests\Fixtures\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Envor\Datastore\Tests\Fixtures\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Envor\Datastore\Tests\Fixtures\User>
     */
    protected $model = \Envor\Datastore\Tests\Fixtures\User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'current_team_id' => Team::factory(),
        ];
    }
}