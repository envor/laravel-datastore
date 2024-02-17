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

    public function __construct(string $name)
    {
        static::booting($name, $this);
        static::boot($name, $this);
        static::booted($name, $this);
    }

    protected static function booting(string $name, self $instance) : void
    {
        //
    }

    protected static function booted(string $name, self $instance) : void
    {
        //
    }

    protected static function boot(string $name, self $instance) : self
    {
        $instance->name = $instance->makeName($name);
        $instance->adminName = $instance->makeAdminName($name);
        $instance->adminConfig = $instance->makeAdminConfig();
        $instance->connectionName = $instance->makeConnectionName($name);
        $instance->config = $instance->makeConfig();
        return $instance;
    }

    protected static function make(string $name) : self
    {
        return new static($name);
    }

    public function prefix(string $prefix = '') : self
    {
        $oldPrefix = $this->namePrefix;
        $this->namePrefix = $prefix;

        static::boot(str()->of($this->name)->replace($oldPrefix, ''), $this);

        return $this;
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

    public function __toString() : string
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

    public function run(callable $callback) : mixed
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

    public function migrate(array $options = []): void
    {
        $this->migrateOptions($options);
        $this->configure();
        $this->callMigrateCommand();
        $this->cleanup();
    }

    public function cleanup(): void
    {
        $this->restoreOriginalDefaultConfig();
        // $this->refreshConnection(config('database.default'));
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
        // $this->refreshConnection($this->adminName);
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
        return trim((string) str()->of($this->makeName($name))->start('admin_'));
    }

    protected function makeName(string $name) : string
    {
        return trim((string) str()->of($name)->slug('_')->start($this->namePrefix), '_');
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
            "database.connections.{$connection}" => $this->adminConfig,
            'database.default' => $connection,
        ]);
    }
}
