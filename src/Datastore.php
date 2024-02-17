<?php

namespace Envor\Datastore;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Datastore
{
    public ?string $connection = null;

    public ?string $adminConnection = null;

    public ?string $migrationPath = null;

    public array $migrateOptions = [];

    public array $config = [];

    public array $adminConfig = [];

    protected bool $prefixed = false;

    public ?OutputInterface $output = null;

    private function __construct(private string $name)
    {
        static::booting($name, $this);
        static::boot($name, $this);
        static::booted($name, $this);
    }

    private function __destruct()
    {
        if ($this->prefixed) {
            config([
                'database.connections' => Arr::except(config('database.connections'), [$this->connection, $this->adminConnection]),
            ]);

            app(DatabaseManager::class)->purge($this->connection);

            return;
        }

        config([
            'database.connections' => Arr::except(config('database.connections'), $this->adminConnection),
        ]);

        app(DatabaseManager::class)->purge($this->adminConnection);
    }

    public static function make(string $name) : static
    {
        return new static($name);
    }

    public static function withPrefix(string $name, string $prefix) : static
    {
        $instance = new static($prefix.$name);

        $instance->prefixed = true;

        return $instance;
    }

    public function exists() : bool
    {
        $this->configureAdmin();

        return (bool) app(DatabaseManager::class)->usingConnection($this->adminConnection,
            fn () => Schema::databaseExists($this->name),
        );
    }

    public function create() : bool|static
    {
        if ($this->exists()) {
            return false;
        }

        if ($this->createDatabase()) {
            return $this;
        }

        return false;
    }

    public function configure() : static
    {
        config([
            "database.connections.{$this->connection}" => $this->config,
        ]);

        app(DatabaseManager::class)->setDefaultConnection($this->connection);

        return $this;
    }

    public function migrate(): void
    {
        $this->configure();
        $this->callMigrateCommand();
    }

    public function migrateOptions(array $options): self
    {
        $this->migrateOptions = $options;

        return $this;
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

        if (array_key_exists('--fresh', $options)) {
            $command = 'migrate:fresh';
        }

        $options = Arr::except($options, '--fresh');

        Artisan::call($command, $options, $this->output);

        return Artisan::output();
    }



    protected function createDatabase() : bool
    {
        $this->configureAdmin();

       return (bool) app(DatabaseManager::class)->usingConnection($this->adminConnection,
            fn () => Schema::createDatabaseIfNotExists($this->name),
        );
    }

    protected function configureAdmin() : void
    {
        config([
            "database.connections.{$this->adminConnection}" => $this->adminConfig,
        ]);
    }

    protected static function boot(string $name, Datastore $datastore) : void
    {
        $datastore->name = static::makeName($name);
        $datastore->connection = static::makeConnection($name);
        $datastore->adminConnection = static::makeAdminConnection($name);
        $datastore->adminConfig = static::makeAdminConfig($name);
        $datastore->config = static::makeConfig($datastore);
    }

    protected static function makeConfig(Datastore $datastore) : array
    {

        return array_merge($datastore->adminConfig, [
            'name' => $datastore->connection,
            'database' => $datastore->name,
        ]);
    }

    abstract protected static function makeAdminConfig() : array;

    protected static function makeAdminConnection(string $name) : string
    {
        return 'datastore_admin_'.$name;
    }

    protected static function makeConnection(string $name) : string
    {
        return $name;
    }

    protected static function makeName(string $name) : string
    {
        return $name;
    }

    protected static function booting(string $name, Datastore $datastore) : void
    {
        //
    }

    protected static function booted(string $name, Datastore $datastore) : void
    {
        //
    }
}
