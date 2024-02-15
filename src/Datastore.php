<?php

namespace Envor\Datastore;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Datastore
{
    public string $name;

    public string $adminName;

    public string $connectionName;

    public ?string $migratePath = null;

    public ?OutputInterface $output = null;

    public array $migrateOptions = [];

    public array $config;

    public array $adminConfig;

    protected string $namePrefix = 'datastore_';

    abstract protected function makeAdminConfig() : array;

    public function __construct(string $name, string $disk = 'local')
    {
        $this->name = $this->makeName($name);

        $this->adminName = $this->makeAdminName($name);
        
        $this->adminConfig = $this->makeAdminConfig();

        $this->connectionName = $this->makeConnectionName($name);

        $this->config = $this->makeConfig();

    }

    protected function makeConnectionName(string $name) : string
    {
        return $this->makeName($name);
    }

    public function exists(): bool
    {
        return $this->run(fn() => Schema::databaseExists($this->name));
    }

    public function migrateOptions(array $options): self
    {
        $this->migrateOptions = $options;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
    
    public function clearConfigs(): void
    {
        $isDatastoreConfig = function ($value, $key) {
            return str()->startsWith($key, $this->namePrefix, true) || str()->startsWith($key, 'admin_'.$this->namePrefix, true);
        };

        $datastoreConfigs = Arr::where(config('database.connections'), $isDatastoreConfig);

        config([
            'database.connections' => Arr::except(config('database.connections'), array_keys($datastoreConfigs)),
        ]);
    }

    public function run(callable $callback)
    {
        $this->configure();
        $result = $callback();
        $this->cleanup();

        return $result;
    }

    public function output(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function migratePath(string $path): self
    {
        $this->migratePath = $path;

        return $this;
    }

    public function migrate(): void
    {
        $this->configure();
        $this->callMigrateCommand();
    }

    public function cleanup(): void
    {
        $this->restoreOriginalDefaultConfig();
        $this->refreshConnection(config('database.default'));
        $this->clearConfigs();
    }

    protected function callMigrateCommand(): string
    {
        $options = [
            '--force' => true,
        ];

        if ($this->migratePath) {
            $options['--path'] = $this->migratePath;
        }

        $options = array_merge($options, $this->migrateOptions);

        $command = 'migrate';

        if(array_key_exists('--fresh', $options)) {
            $command = 'migrate:fresh';
        }

        $options = Arr::except($options, '--fresh');

        Artisan::call($command, $options, $this->output);

        return Artisan::output();
    }

    public function create(): bool
    {
        $this->clearConfigs();
        $this->cacheOriginalDefaultConfig();
        $this->configureAdmin();
        $this->refreshConnection($this->adminName);
        $created = $this->createDatabase();
        $this->purgeAdmin();
        $this->cleanup();

        return $created;
    }

    protected function purgeAdmin(): void
    {
        DB::purge($this->adminName);
    }

    protected function createDatabase(): bool
    {
        return Schema::createDatabaseIfNotExists($this->name);
    }

    protected function refreshConnection($connectionName): void
    {
        DB::reconnect($connectionName);
    }

    protected function cacheOriginalDefaultConfig(): void
    {
        $key = config('database.default');
        $config = config("database.connections.{$key}");

        cache()->forever('original_default_database', [
            'key' => $key,
            'config' => $config,
        ]);
    }

    protected function restoreOriginalDefaultConfig(): bool
    {
        $original = cache()->get('original_default_database');

        if (! $original) {
            return false;
        }

        config([
            'database.default' => $original['key'],
        ]);

        cache()->forget('original_default_database');

        return true;
    }

    protected function makeAdminName(string $name) : string
    {
        return 'admin_'.$this->makeName($name);
    }

    protected function makeName(string $name) : string
    {
        return $this->namePrefix.$name;
    }

    protected function makeConfig() : array
    {
        $config = $this->adminConfig;

        $config['database'] = $this->name;

        $config['name'] = $this->connectionName;

        return $config;
    }

    protected function configureDatabase() : void
    {
        $connection = $this->connectionName;

        config([
            "database.connections.{$connection}" => $this->config,
        ]);

        config([
            'database.default' => $connection,
        ]);
    }

    public function configure() : void
    {
        $this->clearConfigs();
        $this->cacheOriginalDefaultConfig();
        $this->configureDatabase();
        $this->refreshConnection($this->connectionName);
    }

    public function configureAdmin() : void
    {
        $connection = $this->adminName;

        config([
            'database.default' => $connection,
            "database.connections.{$connection}" => $this->adminConfig,
        ]);
    }
}
