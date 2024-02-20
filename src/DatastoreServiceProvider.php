<?php

namespace Envor\Datastore;

use Envor\Datastore\Commands\DatastoreCommand;
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
        Datastore::configureDatastoresUsing(Models\Datastore::class);
    }
}
