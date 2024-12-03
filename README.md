# Filament ShortURL

![Art](./art.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/a21ns1g4ts/filament-short-url.svg?style=flat-square)](https://packagist.org/packages/a21ns1g4ts/filament-short-url)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/a21ns1g4ts/filament-short-url/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/a21ns1g4ts/filament-short-url/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/a21ns1g4ts/filament-short-url/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/a21ns1g4ts/filament-short-url/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/a21ns1g4ts/filament-short-url.svg?style=flat-square)](https://packagist.org/packages/a21ns1g4ts/filament-short-url)


### Filament for https://github.com/ash-jc-allen/short-url

## Installation

You can install the package via composer:

```bash
composer require a21ns1g4ts/filament-short-url
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="AshAllenDesign\ShortURL\Providers\ShortURLProvider"
php artisan migrate
```

## Install for Panel
```php
->plugins([
    \A21ns1g4ts\FilamentShortUrl\FilamentShortUrlPlugin::make()
])
```
## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [a21ns1g4ts](https://github.com/a21ns1g4ts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
