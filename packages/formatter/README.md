# The Formatter

This package provides a customizable wrapper around Intl formatters.

[include:file]: ../../docs/shared/Requirements.md
[//]: # (start: 4aa299d1fd76a742656b8ab1b15d0ae7f7026ef1)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.5.2 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |  `HEAD`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 4aa299d1fd76a742656b8ab1b15d0ae7f7026ef1)

# Installation

```shell
composer require lastdragon-ru/lara-asp-formatter
```

# Usage & Configuration

Formatter is very simple to use:

```php
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Formatter\Formatter;

$formatter = Container::getInstance()->make(Formatter::class); // For default app locale
$formatter = $formatter->forLocale('ru_RU'); // For ru_RU locale

$formatter->string(123);        // '123'
$formatter->decimal(123.45);    // '123,45'
```

Please check [source code](./src/Formatter.php) to see available methods and [config example](./config/config.php) to available settings 🤗

[include:file]: ../../docs/shared/Contributing.md
[//]: # (start: 777f7598ee1b1a8c8fe67be6a3b7fce78a6e687e)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 777f7598ee1b1a8c8fe67be6a3b7fce78a6e687e)
