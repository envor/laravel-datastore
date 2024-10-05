<?php

use Envor\Datastore\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class)->in(__DIR__);

function createRequest($method, $uri): \Illuminate\Http\Request
{

    $symphonyRequest = \Symfony\Component\HttpFoundation\Request::create($uri, $method);

    return \Illuminate\Http\Request::createFromBase($symphonyRequest);
}

function teamsAndUsersSchema()
{
    Schema::connection(config('database.platform'))->create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->foreignId('current_team_id')->nullable();
        $table->string('profile_photo_path', 2048)->nullable();
        $table->timestamps();
    });

    Schema::connection(config('database.platform'))->create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->foreignId('datastore_id')->index()->nullable();
        $table->foreignId('user_id')->index()->nullable();
        $table->timestamps();
    });
}
