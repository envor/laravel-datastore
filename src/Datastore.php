<?php

namespace Envor\Datastore;

use Envor\Datastore\Contracts\ConfiguresDatastore;
use Envor\Datastore\Contracts\HasDatastoreContext;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\OutputInterface;
use WeakMap;

abstract class Datastore
{
    public ?string $connection = null;

    public ?string $adminConnection = null;

    public ?string $migratePath = null;

    public ?OutputInterface $buffer = null;

    public array $migrateOptions = [];

    public array $config = [];

    public array $adminConfig = [];

    protected mixed $result = null;

    protected bool $prefixed = false;

    public static function configureDatastoresUsing(string $class): void
    {
        app()->singleton(ConfiguresDatastore::class, $class);
    }

    public static function configureDatastoreContextUsing(string $class): void
    {
        app()->singleton(HasDatastoreContext::class, $class);
    }

    public static function fake()
    {
        app()->instance('datastore_faking', new WeakMap([
            'faking' => true,
        ]));
    }

    public static function stopFaking()
    {
        app()->forgetInstance('datastore_faking');
    }

    public static function faking()
    {
        if (app()->has('datastore_faking')) {
            return true;
        }

        return config('datastore.creates_databases', false);
    }

    private function __construct(public string $name, protected ?string $prefix = null)
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

        return $this;
    }

    protected function cachePreviousDefault(): void
    {
        $key = config('database.default');
        $config = config("database.connections.{$key}");

        cache()->forever('previous_default_database', [
            'key' => $key,
            'config' => $config,
        ]);
    }

    protected function popConfig(): void
    {
        $this->restorePreviousDefault();
    }

    protected function restorePreviousDefault(): void
    {
        $previous = cache()->get('previous_default_database');

        config([
            "database.connections.{$previous['key']}" => $previous['config'],
            'database.default' => $previous['key'],
        ]);

        // app('db')->extend($previous['key'], function ($config, $name) use ($previous) {
        //     return app('db.factory')->make($previous['config'], $name);
        // });

        // DB::purge($previous['key']);

        // // Octane will have an old `db` instance in the Model::$resolver.
        // Model::setConnectionResolver(app('db'));
    }

    protected function pushConfig(): void
    {
        $this->cachePreviousDefault();

        config([
            "database.connections.{$this->connection}" => $this->config,
            'database.default' => $this->connection,
        ]);

        // app('db')->extend($this->connection, function ($config, $name) {
        //     return app('db.factory')->make($this->config, $name);
        // });

        // DB::purge($this->connection);

        // // Octane will have an old `db` instance in the Model::$resolver.
        // Model::setConnectionResolver(app('db'));
    }

    public function run(?callable $callback): mixed
    {
        $this->pushConfig();
        $this->result = $callback();
        $this->popConfig();

        return $this;
    }

    public function return(?callable $callback = null): mixed
    {
        return isset($callback) ? $callback($this->result) : $this->result;
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

    public function buffer(OutputInterface $buffer): static
    {
        $this->buffer = $buffer;

        return $this;
    }

    protected function callMigrateCommand(): void
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

        Artisan::call($command, $options, $this->buffer);

        $this->result = Artisan::output();
    }

    public function disconnect(): static
    {
        app(DatabaseManager::class)->purge($this->connection);
        app(DatabaseManager::class)->purge($this->adminConnection);

        DB::disconnect($this->connection);
        DB::disconnect($this->adminConnection);

        return $this;
    }

    protected function createDatabase(): bool
    {

        if ($this->faking() || $this->name === ':memory:') {

            return true;
        }

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
        $datastore->name = static::makeNameIfNotFaking($name);
        $datastore->connection = static::makeConnection($name);
        $datastore->adminConnection = static::makeAdminConnection($name);
        $datastore->adminConfig = static::makeAdminConfigIfNotFaking($datastore);
        $datastore->config = static::makeConfig($datastore);
    }

    public function schema(): ?Builder
    {
        if ($this->exists()) {
            return Schema::connection($this->connection);
        }

        return Schema::connection($this->adminConnection);
    }

    public function createTable(string $table, callable $callback): static
    {
        if ($this->schema()->hasTable($table)) {
            return $this;
        }
        $this->result = $this->schema()->create($table, fn (Blueprint $table) => $callback($table));

        return $this;
    }

    public function tables(): ?Collection
    {
        return collect($this->schema()?->getTables());
    }

    public function db(): ?Connection
    {
        if ($this->exists()) {
            return DB::connection($this->connection);
        }

        return DB::connection($this->adminConnection);
    }

    protected static function makeConfig(Datastore $datastore): array
    {
        if (static::faking()) {
            return config('database.connections.'.config('database.default'));
        }

        return array_merge($datastore->adminConfig, [
            'name' => $datastore->connection,
            'database' => $datastore->name,
        ]);
    }

    protected static function makeAdminConfigIfNotFaking(Datastore $datastore): array
    {
        if (static::faking()) {
            return config('database.connections.'.config('database.default'));
        }

        return static::makeAdminConfig($datastore);
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

    protected static function makeNameIfNotFaking(string $name): string
    {
        if (static::faking()) {
            if (str()->of($name)->contains(':memory:')) {
                return ':memory:';
            }

            return config('database.connections.'.config('database.default').'.database');
        }

        return static::makeName($name);
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
