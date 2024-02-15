<?php

namespace Envor\Datastore;

use Envor\Datastore\Commands\DatastoreCommand;
use Envor\Datastore\Databases\SQLite;
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
            ->hasMigration('create_laravel-datastore_table')
            ->hasCommand(DatastoreCommand::class);
    }

    public function packageBooted()
    {

        if (! isset($_SERVER['LARAVEL_OCTANE'])) {

            return;
        }

        Event::listen(fn (RequestTerminated $requestTerminated) => (new SQLite(':memory:'))->cleanup());
    }
}
