# The Formatter

> This package is the part of Awesome Set of Packages for Laravel.
> 
> [Read more](../../readme.md).

This package provides a customizable wrapper around Intl formatters.


# Installation

```shell
composer require lastdragon-ru/lara-asp-formatter
```


# Usage & Configuration

Formatter is very simple to use:

```php
use LastDragon_ru\LaraASP\Formatter\Formatter;

$formatter = app()->make(Formatter::class); // For default app locale
$formatter = $formatter->forLocale('ru_RU'); // For ru_RU locale

$formatter->string(123);        // '123'
$formatter->decimal(123.45);    // '123,45'
```

Please check [source code](./src/Formatter.php) to see available methods and [config example](./config/config.php) to available settings 🤗
