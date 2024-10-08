<?php

namespace Envor\Datastore;

use Envor\Datastore\Commands\DatastoreCommand;
use Envor\Datastore\Contracts\HasDatastoreContext;
use Illuminate\Support\Arr;
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
        $this->app->terminating(fn () => DatabaseFactory::cleanupRepository());

        if (config('datastore.autoconfigure_default_context', false)) {
            Datastore::configureDatastoreContextUsing(DatastoreContext::class);
        }

        $this->app->booted(function (): void {
            /** @var Router $router */
            $router = $this->app['router'];
            if (config('datastore.push_middleware', false)) {
                $router->pushMiddlewareToGroup('web', DatastoreContextMiddleware::class);
            }
            $router->aliasMiddleware('datastore.context', DatastoreContextMiddleware::class);

            if (class_exists('\Livewire\Volt\Volt') && (! $this->app->runningInConsole() || $this->app->runningUnitTests())) {
                $paths = [
                    __DIR__.'/../resources/views/livewire',
                    __DIR__.'/../resources/views/pages',
                ];
                \Livewire\Volt\Volt::mount($paths);
            }
        });

        $router = $this->app['router'];
        $router->get('/datastore-context', function () {
            return response()->json(Arr::except(app(HasDatastoreContext::class)->datastoreContext()?->database()->config ?? [], 'password', 'username'));
        })->middleware(['web', 'datastore.context']);
    }
}
