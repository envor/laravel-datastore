<?php

namespace Envor\Datastore;

use Envor\Datastore\Commands\DatastoreCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Laravel\Octane\Events\RequestTerminated;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DatastoreServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-datastore')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations(['platform/create_datastores_table', 'platform/create_teams_table'])
            ->hasCommand(DatastoreCommand::class);
    }

    public function packageBooted()
    {

        // if (! isset($_SERVER['LARAVEL_OCTANE'])) {

        //     return;
        // }

        // Event::listen(function (RequestTerminated $requestTerminated) {
        //     $configs = app('db.memory')->table('datastores')->pluck('name')->toArray();

        //     config(['database.connections' => Arr::except(config('database.connections'), $configs)]);
        // });
    }
}
