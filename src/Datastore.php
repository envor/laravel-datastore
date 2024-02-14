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

    public ?string $migrationPath = null;

    public ?OutputInterface $output = null;

    public array $migrateOptions = [];

    public array $config;

    public array $adminConfig;

    protected string $namePrefix = 'datastore_';

    abstract protected function makeAdminConfig();

    public function __construct(string $name, string $disk = 'local')
    {
        $this->name = $this->makeName($name);

        $this->config = $this->makeConfig();

        $this->adminConfig = $this->makeAdminConfig();

        $this->adminName = $this->makeAdminName($name);
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

    public function __destruct()
    {
        $this->cleanup();
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

    public function migrationPath(string $path): self
    {
        $this->migrationPath = $path;

        return $this;
    }

    public function migrate(): void
    {
        $this->configure();
        $this->callMigrateCommand();
        $this->cleanup();
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

        if ($this->migrationPath) {
            $options['--path'] = $this->migrationPath;
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

    public function create(): void
    {
        $this->clearConfigs();
        $this->cacheOriginalDefaultConfig();
        $this->configureAdmin();
        $this->refreshConnection($this->adminName);
        $this->createDatabase();
        $this->purgeAdmin();
        $this->cleanup();
    }

    protected function purgeAdmin(): void
    {
        DB::purge($this->adminName);
    }

    protected function createDatabase(): void
    {
        Schema::connection($this->adminName)->createDatabaseIfNotExists($this->name);
    }

    protected function refreshConnection($connectionName): void
    {
        DB::purge($connectionName);
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
            "database.connections.{$original['key']}" => $original['config'],
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

        return $config;
    }

    protected function configureDatabase() : void
    {
        $connection = $this->name;

        config([
            'database.default' => $connection,
            "database.connections.{$connection}" => $this->config,
        ]);
    }

    public function configure() : void
    {
        $this->clearConfigs();
        $this->cacheOriginalDefaultConfig();
        $this->configureDatabase();
        $this->refreshConnection($this->name);
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
