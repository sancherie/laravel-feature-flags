# A Laravel feature-flags package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sancherie/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/sancherie/laravel-feature-flags)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/sancherie/laravel-feature-flags/run-tests?label=tests)](https://github.com/sancherie/laravel-feature-flags/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/sancherie/laravel-feature-flags/Check%20&%20fix%20styling?label=code%20style)](https://github.com/sancherie/feature/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sancherie/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/sancherie/laravel-feature-flags)

## Installation

You can install the package via composer:

```bash
composer require sancherie/laravel-feature-flags
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="feature-flags-migrations"
php artisan migrate
```

## Usage

Read more about usage on the [Wiki](https://github.com/sancherie/laravel-feature-flags/wiki).

## Testing
Use the following command to run test:
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Clément SANCHEZ](https://github.com/sancherie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
