<?php

namespace Envor\Datastore\Tests;

use Envor\Datastore\DatastoreServiceProvider;
use Envor\Platform\PlatformServiceProvider;
use Envor\SchemaMacros\SchemaMacrosServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Livewire\Volt\VoltServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Envor\\Datastore\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            PlatformServiceProvider::class,
            DatastoreServiceProvider::class,
            SchemaMacrosServiceProvider::class,
            LivewireServiceProvider::class,
            VoltServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.platform', 'testing');

        $migration = include __DIR__.'/../database/migrations/platform/create_datastores_table.php.stub';
        $migration->up();
    }
}
