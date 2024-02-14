<?php

namespace Envor\Datastore;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Envor\Datastore\Commands\DatastoreCommand;

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
}
