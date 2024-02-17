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

    public ?string $migratePath = null;

    public ?OutputInterface $output = null;

    public array $migrateOptions = [];

    public array $config = [];

    public array $adminConfig = [];

    protected bool $prefixed = false;

    private function __construct(private string $name, protected ?string $prefix = null)
    {
        static::booting($name, $this, $prefix);
        static::boot($name, $this, $prefix);
        static::booted($name, $this, $prefix);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public static function withPrefix(string $name, string $prefix): static
    {
        $instance = static::make($prefix.'_'.$name);

        $instance->prefixed = true;

        return $instance;
    }

    public function exists(): bool
    {
        $this->pushAdminConfig();

        return (bool) app(DatabaseManager::class)->usingConnection($this->adminConnection,
            fn () => Schema::databaseExists($this->name),
        );
    }

    public function create(): bool|static
    {
        if ($this->exists()) {
            return $this;
        }

        if ($this->createDatabase()) {
            return $this;
        }

        return false;
    }

    public function configure(): static
    {
        $this->pushConfig();

        app(DatabaseManager::class)->setDefaultConnection($this->connection);

        return $this;
    }

    protected function pushConfig(): void
    {
        config([
            "database.connections.{$this->connection}" => $this->config,
        ]);
    }

    public function run(?callable $callback = null): mixed
    {
        $this->pushConfig();

        return app(DatabaseManager::class)->usingConnection($this->connection, $callback);
    }

    public function migratePath(string $path): static
    {
        $this->migratePath = $path;

        return $this;
    }

    public function migrate(?array $options = null): static
    {
        if ($options) {
            $this->migrateOptions($options);
        }
        $this->run(fn () => $this->callMigrateCommand());

        return $this;
    }

    public function migrateOptions(array $options): static
    {
        $this->migrateOptions = $options;

        return $this;
    }

    public function output(OutputInterface $output): static
    {
        $this->output = $output;

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

    protected function createDatabase(): bool
    {
        $this->pushAdminConfig();

        return (bool) app(DatabaseManager::class)->usingConnection($this->adminConnection,
            fn () => Schema::createDatabaseIfNotExists($this->name),
        );
    }

    protected function pushAdminConfig(): void
    {
        config([
            "database.connections.{$this->adminConnection}" => $this->adminConfig,
        ]);
    }

    protected static function boot(string $name, Datastore $datastore): void
    {
        $datastore->name = static::makeName($name);
        $datastore->connection = static::makeConnection($name);
        $datastore->adminConnection = static::makeAdminConnection($name);
        $datastore->adminConfig = static::makeAdminConfig($datastore);
        $datastore->config = static::makeConfig($datastore);
    }

    protected static function makeConfig(Datastore $datastore): array
    {

        return array_merge($datastore->adminConfig, [
            'name' => $datastore->connection,
            'database' => $datastore->name,
        ]);
    }

    abstract protected static function makeAdminConfig(Datastore $datastore): array;

    protected static function makeAdminConnection(string $name): string
    {
        return 'datastore_admin_'.$name;
    }

    protected static function makeConnection(string $name): string
    {
        return $name;
    }

    protected static function makeName(string $name): string
    {
        return $name;
    }

    protected static function booting(string $name, Datastore $datastore): void
    {
        //
    }

    protected static function booted(string $name, Datastore $datastore): void
    {
        //
    }
}
