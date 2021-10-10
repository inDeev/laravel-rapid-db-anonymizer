# Laravel Rapid DB Anonymizer

[![Latest Stable Version](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/v)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer) [![Total Downloads](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/downloads)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer) [![Latest Unstable Version](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/v/unstable)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer) [![License](http://poser.pugx.org/indeev/laravel-rapid-db-anonymizer/license)](https://packagist.org/packages/indeev/laravel-rapid-db-anonymizer)

Package automatically anonymizes sensitive data through database.

## Installation

You can install the package via composer:

```bash
composer require indeev/laravel-rapid-db-anonymizer
```

## Usage

On any model which is using sensitive data add `use Anonymizable` and constant `const ANONYMIZABLE = [];` e.g.:
```php
class Customer 
{
    use Anonymizable;
    
    // CONSTANTS

    const ANONYMIZABLE = [
        'name' => [
            'faker' => ['provider' => 'firstName'],
        ],
    ];
    // ...
}
```

`ANONYMIZABLE` is defined as array of `'column name' => [what to do]` values.

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
- if value of setTo is an array type, it is converted to json string. E.g. `['foo' => 'bar']` is converted to `{"foo":"bar"}`
- if value of setTo is `null`, it is converted to NULL. Pay special attention that the column must be nullable in this case.
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
By default, NULL values are retained, if you want to anonymize them also, just use `anonymizeNull` switch for specified column.
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
Run anonymization of all models with anonymization trait
```cmd
php artisan db:anonymize
```
Run anonymization of specific model's table
```cmd
php artisan db:anonymize --model=\\App\\Models\\VerificationCode
```
Run anonymization of specific columns in all models where are specified columns defined in `ANONYMIZABLE` constant.
```cmd
php artisan db:anonymize --columns=name,surname
```
Run anonymization of specific columns in specific model's table
```cmd
php artisan db:anonymize --model=\\App\\Models\\VerificationCode --columns=name,surname
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
