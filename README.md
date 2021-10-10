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
        'name' => 'firstName',
        'surname' => 'lastName',
        'born_date' => 'date',
        'personal_in' => 'numerify|######/####',
        'phone' => 'phone',
        'email' => 'email',
        'note' => 'null',
        'ID_number' => 'numerify|#########',
        'authorization_code' => 'word',
    ];
    // ...
}
```

`ANONYMIZABLE` constant is defined as array of `column_name => faker_property/method`. If Faker's method with argument is required, argument must be divided from method name by pipe character (`|`). E.g. to use `faker->numerify('###')`, `'numerify|###'` is used. 

```cmd
php artisan db:anonymize
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
