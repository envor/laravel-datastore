# On Prem Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/envor/laravel-datastore.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-datastore)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-datastore/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/envor/laravel-datastore/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/envor/laravel-datastore/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/envor/laravel-datastore/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/envor/laravel-datastore.svg?style=flat-square)](https://packagist.org/packages/envor/laravel-datastore)

A simple strategy for handling dynamic databases of varying types

## Installation

You can install the package via composer:

```bash
composer require envor/laravel-datastore
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="datastore-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="datastore-config"
```

This is the contents of the published config file:

```php
return [
    'model' => \Envor\Datastore\Models\Datastore::class,
];
```

## Usage

You actually don't need to use the model or even run migrations. You can use the factory directly like so:

```php
use Envor\Datastore\DatabaseFactory;

$sqlite = DatabaseFactory::newDatabase('mydb', 'sqlite');

// Envor\Datastore\Databases\SQLite {#2841 ...

$sqlite->create();

// true

$sqlite->name;

// ...storage/app/datastore/mydb.sqlite

$sqlite->connectionName;

// mydb

$sqlite->migrate();

  //  INFO  Preparing database.  

  // Creating migration table ................ 9.55ms DONE

$sqlite->configure();


config('database.default');

// "mydb"

config('database.connections.mydb');

// [
//     "driver" => "sqlite",
//     "url" => null,
//     "database" => "...storage/app/datastore/mydb.sqlite",
//     "prefix" => "",
//     "foreign_key_constraints" => true,
//     "name" => "mydb",
// ]
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [inmanturbo](https://github.com/envor)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
