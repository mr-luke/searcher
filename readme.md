Searcher - Laravel Eloquent query Package.
==============

This package is responsible for converting OData(-like) URL Query into SQL query on top of the Eloquent Builder.

* [Getting Started](#getting-started)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Plans](#plans)

## Getting Started

Searcher has been developed using Laravel 5.5. It's recommended to test it out before using with previous versions. PHP >= 7.1.3 is required.

## Installation

To install through composer, simply put the following in your composer.json file and run `composer update`

```json
{
    "require": {
        "mr-luke/searcher": "~1.0"
    }
}
```
Or use the following command

```bash
composer require "mr-luke/searcher"
```

Next, add the service provider to `app/config/app.php`

```
Mrluke\Searcher\SearcherServiceProvider::class,
```

## Configuration

You can see the options for further customization in the [config file](config/searcher.php).

You can also publish config file
```bash
php artisan vendor:publish
```

## Usage

#### Step 1: Model

To use `Searcher` you need to setup your `Searchable` Eloquent model before. Add following interface to the model `Mrluke\Searcher\Contracts\Searchable` and create method:

```php
/**
 * Determines rules for Searcher.
 *
 * @return array
 */
public static function getSearchableConfig() : array
{
	return [
    	'filter' => ['first' => 'firstName'],
        'query' => ['first', 'last'],
        'sort => ['age' => 'age'],
    ];
}
```

* `filter` - this property defines fields allowed to filter by, eg URL: `first=john,or+steve`
* `query` - this property defines fields allowed to query by, eg URL: `q=lorem`
* `sort` - this property defines fields allowed to sort by, eg URL: `sort=+first,-age`

`['first' => 'firstName']` is this example `first` is public key (URL) and `firstName` is an Eloquent attribute.

You can also use `Mrluke\Searcher\Traits\Searchable` that gives ability to read configuration from class property instead of function.

```php
/**
 * Searcher configuration.
 *
 * @var array
 */
protected static $searchableConfig = [];
```

#### Step 2: Controller

You can access `Searcher` via Facade `Mrluke\Searcher\Facades\Searcher`. All you need is following line

```php
$collection = Searcher::setModel(User::class)->get();
```

##### setModel($model, `Builder` $builder = null) : `self`

This is main method of package. It is required to perform any action.

* `$model - string|array`
* `$builder - Illuminate\Database\Eloquent\Builder|null`

This method provides ability to setup `Searcher` is tree different ways.

1. Default Model `searchableConfig` with no additional SQL requirements.
2. Default Model `searchableConfig` with custom `Builder` (eg: `whereIn` condition not depended on Url query)
3. Custom `searchableConfig` passed as 1st argument with Model `Builder` instance.

By default `Seracher` uses `Illuminate\Http\Request::all()` as an input array.

##### setQuery(`array` $inputs) : `self`

This method allows you to set own inputs array instead of using default `Illuminate\Http\Request::all()`

##### setOptions(`array` $options) : `self`

This method allows you to override default `Searcher` configuration. If you'd like to check all available options see [config file](config/searcher.php).

##### getBuilder() : `Builder`

This method returns prepared Builder instance for given inputs.

##### get() : `Collection`

This method returns `Illuminate\Support\Collection` for given inputs. If your configuration has property `auto_pagination = true`, it will perform pagination based on `limit` & `offset` inputs.

##### paginate(`int` $limit = null, `int` $offset = null) : `mixed`

This method allows you to get paginated collection of models. If parameters are not set, method will get them automatically from the inputs. If your configuration has property `api_mode = true`, method will return `Illuminate\Support\Collection` otherwise you will get `Illuminate\Pagination\LengthAwarePaginator`.

## Plans

Full text contextual searching.
