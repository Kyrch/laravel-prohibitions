# Prohibit your models 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kyrch/laravel-prohibitions.svg?style=flat-square)](https://packagist.org/packages/kyrch/laravel-prohibitions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kyrch/laravel-prohibitions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kyrch/laravel-prohibitions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/kyrch/laravel-prohibitions/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/kyrch/laravel-prohibitions/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kyrch/laravel-prohibitions.svg?style=flat-square)](https://packagist.org/packages/kyrch/laravel-prohibitions)

## Description

Laravel Prohibitions is a Laravel package that allows you to prohibit users from executing specific actions for a determined period of time.

Instead of relying on hard-coded checks or ad-hoc flags, this package introduces a sanction-based system where actions can be explicitly denied through well-defined rules.

The package also provides a Sanction model, which works as a preset of prohibited actions, making it easy to group multiple prohibitions under a single disciplinary rule — such as temporary bans, feature restrictions, or full account suspensions.

## Installation

You can install the package via composer:

```bash
composer require kyrch/laravel-prohibitions
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-prohibitions-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-prohibitions-config"
```

### Usage

#### Setup

Add the `HasSanctions` trait to your User model:

```php
use Kyrch\Prohibition\Traits\HasSanctions;

class User extends Authenticatable
{
    use HasSanctions;
}
```

You need to create your sanctions and prohibitions first. For example:

```php
$prohibition = Prohibition::query()->create(['name' => 'update post']);

$sanction = Sanction::query()->create(['name' => 'posts']);

$prohibition->sanctions()->attach($sanction);
```

#### How to prohibit

Now you can use methods this packages provides, such as:

```php
# Prohibit the user from updating any posts.
$user->prohibit('update post', now()->addWeek());

# Similar to roles, you can create a kind of "ban preset" that has a group of prohibitions.
$user->applySanction('posts', now()->addWeek());

# Check if user is prohibited from updating any post.
$user->isProhibitedFrom('update post');
```

It is recommended to use the `isProhibitedFrom()` method in your `Gate::before` or
the `before` method in your policy class.

#### Events

There are two events: `ModelProhibitionTriggered` and `ModelSanctionTriggered`.
If you don't want them enabled, you can disable it in the published config file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kyrch](https://github.com/Kyrch)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
