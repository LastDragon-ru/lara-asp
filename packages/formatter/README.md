# Formatter

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |  `HEAD`  ,  `5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)

# Installation

```shell
composer require lastdragon-ru/lara-asp-formatter
```

# Usage

Formatter is very simple to use:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: caf4823c1825389ee306092d37b26a07d291dc0264dff1977be86e61ca455a97)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Formatter;

$default = Container::getInstance()->make(Formatter::class); // For default app locale
$locale  = $default->forLocale('ru_RU');                     // For ru_RU locale

Example::dump($default->integer(123.454321));
Example::dump($default->decimal(123.454321));
Example::dump($locale->decimal(123.454321));
```

The `$default->integer(123.454321)` is:

```plain
"123"
```

The `$default->decimal(123.454321)` is:

```plain
"123.45"
```

The `$locale->decimal(123.454321)` is:

```plain
"123,45"
```

[//]: # (end: caf4823c1825389ee306092d37b26a07d291dc0264dff1977be86e61ca455a97)

Please check [source code](./src/Formatter.php) to see available methods and [config example](defaults/config.php) to available settings 🤗

[include:file]: ../../docs/shared/Contributing.md
[//]: # (start: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)
