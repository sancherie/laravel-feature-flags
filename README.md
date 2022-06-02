# A Laravel feature-flags package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sancherie/feature.svg?style=flat-square)](https://packagist.org/packages/sancherie/feature)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/sancherie/feature/run-tests?label=tests)](https://github.com/sancherie/feature/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/sancherie/feature/Check%20&%20fix%20styling?label=code%20style)](https://github.com/sancherie/feature/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sancherie/feature.svg?style=flat-square)](https://packagist.org/packages/sancherie/feature)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/Feature.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/Feature)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can
support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards
on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require sancherie/laravel-feature-flags
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="feature-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="feature-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="feature-views"
```

## Usage

This packages offers three different ways to enable a feature flag.

- Enable a feature globally in database
- Enable a feature specifically to one featurable subject (e.g: a User)
- Enable a feature programmatically

### Global features
To enable a feature globally, run the following command:

```bash
php artisan feature:enable "my-new-feature"
```

You can also enable it programmatically or using Tinker with the Facade:
```php
Sancherie\Features\Facades\Feature::enable('my-new-feature');
```

Check that the feature has been enabled with the following code:
```php
Sancherie\Features\Facades\Feature::enabled('my-new-feature'); // true
Sancherie\Features\Facades\Feature::isEnabled('my-new-feature'); // true
Sancherie\Features\Facades\Feature::isGloballyEnabled('my-new-feature'); // true
```

### Specific (or direct) features

You can also enable a feature for a given featurable subject (e.g: a User model).

#### Featurable implementation

Before that, the subject must implement the `Sancherie\Feature\Contracts\Featurable` interface. For Eloquent models, the
helper trait `Sancherie\Feature\Helpers\WithFeatures` easily implement that interface for you.
It creates a polymorphic relation between the `features` table and the featurable model:

```php
class User extends \Illuminate\Database\Eloquent\Model implements \Sancherie\Feature\Contracts\Featurable
{
    use \Sancherie\Feature\Helpers\WithFeatures;
}
```

#### Enable a specific feature

Once you have implemented the interface, there are several ways to enable a feature for a specific subject:

- Through the subject

```php
$user = User::find('342f5a57-a163-4c8d-9747-1a8b375194ad');

$user->giveFeature('my-new-feature');
```

- Using the facade:

```php
$user = User::find('342f5a57-a163-4c8d-9747-1a8b375194ad');

Sancherie\Features\Facades\Feature::enable('my-new-feature', $user);
```

- Using the command

```bash
php artisan feature:enable --feature my-new-feature --model User --id342f5a57-a163-4c8d-9747-1a8b375194ad
```

Finally, check that the feature is correctly enabled:

```php
$user = User::find('342f5a57-a163-4c8d-9747-1a8b375194ad');

Sancherie\Features\Facades\Feature::enabled('my-new-feature', $user); // true
Sancherie\Features\Facades\Feature::isEnabled('my-new-feature', $user); // true
Sancherie\Features\Facades\Feature::isSpecificallyEnabled('my-new-feature'); // true
```

#### Disable a specific feature

You are free to disable a specific feature in several ways:

- Through the subject

```php
$user = User::find('342f5a57-a163-4c8d-9747-1a8b375194ad');

$user->revokeFeature('my-new-feature');
```

- Using the facade

```php
$user = User::find('342f5a57-a163-4c8d-9747-1a8b375194ad');

Sancherie\Features\Facades\Feature::disable('my-new-feature', $user);
```

- Using the command

```bash
php artisan feature:disable --feature my-new-feature --model User --id342f5a57-a163-4c8d-9747-1a8b375194ad
```

### Programmatic features
There is a last way to enable a feature: directly into the code (no database usage).

#### Declare an enabled feature
To declare an enabled feature, write the following in your during the boot of your application:

```php
public function boot()
{
    Sancherie\Feature\Facades\Feature::declare('my-new-feature');
}
```

You can also pass a boolean as a second argument to conditionally enable a feature:
```php
public function boot()
{
    // Feature not enabled
    Sancherie\Feature\Facades\Feature::declare('my-disabled-feature', false); 
    // Feature enabled everywhere except on production
    Sancherie\Feature\Facades\Feature::declare('my-new-feature', config('app.env') !== 'production'); 
}
```

#### Declare a feature with a callback condition
For more advanced usage, you can declare a programmatic feature with a callback as a second argument. This way, the feature check becomes lazy.
```php
public function boot()
{
    // Feature enabled only if the request IP is 192.168.45.45
    Sancherie\Feature\Facades\Feature::declare('my-disabled-feature', function () {
        return \Illuminate\Support\Facades\Request::ip() === '192.168.45.45'
    }); 
}
```

An instance of `Sancherie\Feature\Contracts\Featurable` can be passed as a first callback argument to enable a feature 
conditionally using featurable subjects:
```php
public function boot()
{
    // The feature will be enabled only if the user email finishes by the good domain name.
    Sancherie\Feature\Facades\Feature::declare('my-disabled-feature', function (User $user) {
        return str_ends_with($user->email, '@my-company.com');
    }); 
}
```

> Warning: the first callback argument MUST implement the `Sancherie\Feature\Contracts\Featurable` interface in
> order to be called.


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
