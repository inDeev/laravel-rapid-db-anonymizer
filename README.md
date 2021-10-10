# Laravel Rapid DB Anonymizer

[![Latest Stable Version](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/v)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer)
[![Total Downloads](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/downloads)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer)
[![Latest Unstable Version](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/v/unstable)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer)
[![License](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/license)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer)

The package rapidly anonymizes large amounts of sensitive data throughout the database.

## Installation

You can install the package through the composer:

```bash
composer require indeev/laravel-rapid-db-anonymizer
```

## Usage

In any model that contains sensitive data `use Anonymizable;` trait and `const ANONYMIZABLE = [];` constant.
```php
class Customer 
{
    use Anonymizable;

    const ANONYMIZABLE = [
        'name' => [
            'faker' => ['provider' => 'firstName'],
        ],
    ];
    // ...
}
```

`ANONYMIZABLE` is defined as an array of `'column_name' => [what_to_do]` values.

### Prepare ANONYMIZABLE constant

Truncate table
```php
const ANONYMIZABLE = ['truncate'];
```
**Replace column value with faker's provider** (without parameters)
```php
const ANONYMIZABLE = [
        'name' => [
            'faker' => ['provider' => 'firstName'],
        ],
        // next columns
    ];
 ```
**Replace column value with faker's provider** (with parameters)
```php
const ANONYMIZABLE = [
        'secret_code' => [
            'faker' => ['provider' => 'randomNumber', 'params' => [6, true]],
        ],
        // next columns
    ];
 ```
**Replace with exact value**  
- if value of setTo is an array type, it is converted to json string. For instance `['foo' => 'bar']` is converted to `{"foo":"bar"}`.
- if value of setTo is `null`, it is converted to NULL. Pay special attention that column is set as _nullable_.
```php
const ANONYMIZABLE = [
        'favorite_politician' => [
            'setTo' => 'CONFIDENTIAL',
        ],
        'favorite_numbers' => [
            'setTo' => [7, 13],
        ],
        'favorite_meals' => [
            'setTo' => null,
        ],
        // next columns
    ];
 ```
**Replace also NULL values**  
By default, NULL values are skipped. If you also want to anonymize them, you must add `anonymizeNull` to the column with a value `true`.
```php
const ANONYMIZABLE = [
        'shipping_address' => [
            'faker' => ['provider' => 'address'],
            'anonymizeNull' => true,
        ],
        // next columns
    ];
 ```

### Run anonymization
To run anonymization for the entire database (all models with Anonymizable trait) use
```cmd
php artisan db:anonymize
```
To run anonymization over a specific model use command with `--model=` option
```cmd
php artisan db:anonymize --model=\\App\\Models\\VerificationCode
```
To run anonymizing specific columns in the entire database use command with `--columns=` option. Individual column names must be separated by comma
```cmd
php artisan db:anonymize --columns=name,surname
```
Use a combination of the previous two options to anonymize specific columns above a specific model
```cmd
php artisan db:anonymize --model=\\App\\Models\\User --columns=name,surname
```

## Testing

```bash
composer test
```

## Config

You can export config file by:

```php
php artisan vendor:publish --provider="Indeev\LaravelRapidDbAnonymizer\LaravelRapidDbAnonymizerServiceProvider"
```

In config, you can modify:
 - Chunk size _(default: 500)_ - all 500 rows are updated by one query
 - Forbidden environments _(default: production, prod)_
 - Custom model directory _(default: app/Models)_
 - Custom model namespace _(default: \App\Models\)_
 - Faker's locale _(default: en_US)_

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email katerinak@indeev.eu instead of using the issue tracker.

## Credits

-   [Petr Katerinak](https://github.com/indeev)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
