# (Laravel) Intl Formatter

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters to use it inside Laravel application.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `6.4.2 ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 2.1.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "formatter"}})
[//]: # (start: preprocess/8750339286f08805)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-formatter
```

[//]: # (end: preprocess/8750339286f08805)

# Usage

Formatter is very simple to use:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Formatter;

$default = app()->make(Formatter::class); // For default app locale
$locale  = $default->forLocale('ru_RU');  // For ru_RU locale

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

[//]: # (end: preprocess/4c2bcd97f5d25b12)

Please check [source code](./src/Formatter.php) to see available methods and [config example](defaults/config.php) to available settings 🤗

# Formats

Some methods like as `date()`/`datetime()`/etc have `$format` argument. The argument specifies not the format but the format name. So you can use the names and do not worry about real formats. It is very important when application big/grow. To specify available names and formats the package config should be published and customized.

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Formatter\\Provider --tag=config
```

[include:example]: ./docs/Examples/Config.php
[//]: # (start: preprocess/e30ad70238f2c282)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\Package;

Example::config(Package::Name, [
    'options' => [
        Formatter::Date => 'default',
    ],
    'all'     => [
        Formatter::Date => [
            'default' => 'd MMM yyyy',
            'custom'  => 'yyyy/MM/dd',
        ],
    ],
    'locales' => [
        'ru_RU' => [
            Formatter::Date => [
                'custom' => 'dd.MM.yyyy',
            ],
        ],
    ],
]);

$datetime = Date::make('2023-12-30T20:41:40.000018+04:00');
$default  = app()->make(Formatter::class);
$locale   = $default->forLocale('ru_RU');

Example::dump($default->date($datetime));
Example::dump($default->date($datetime, 'custom'));
Example::dump($locale->date($datetime));
Example::dump($locale->date($datetime, 'custom'));
```

The `$default->date($datetime)` is:

```plain
"30 Dec 2023"
```

The `$default->date($datetime, 'custom')` is:

```plain
"2023/12/30"
```

The `$locale->date($datetime)` is:

```plain
"30 дек. 2023"
```

The `$locale->date($datetime, 'custom')` is:

```plain
"30.12.2023"
```

[//]: # (end: preprocess/e30ad70238f2c282)

# Duration

To format duration you can use built-in Intl formatter, but it doesn't support fraction seconds and have different format between locales (for example, `12345` seconds is `3:25:45` in `en_US` locale, and `12 345` in `ru_RU`). These reasons make difficult to use it in real applications. To make `duration()` more useful, the alternative syntax was added.

[include:docblock]: ./src/Utils/DurationFormatter.php ({"summary": false})
[//]: # (start: preprocess/29da251049347125)
[//]: # (warning: Generated automatically. Do not edit.)

The syntax is the same as [ICU Date/Time format syntax](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).

| Symbol | Meaning                       |
|--------|-------------------------------|
| `y`    | years                         |
| `M`    | months                        |
| `d`    | days                          |
| `H`    | hours                         |
| `m`    | minutes                       |
| `s`    | seconds                       |
| `S`    | fractional seconds            |
| `z`    | negative sign (default `-`)   |
| `'`    | escape for text               |
| `''`   | two single quotes produce one |

[//]: # (end: preprocess/29da251049347125)

[include:example]: ./docs/Examples/Duration.php
[//]: # (start: preprocess/1bbaf6764d0f3cce)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Formatter;

$default = app()->make(Formatter::class); // For default app locale
$locale  = $default->forLocale('ru_RU');  // For ru_RU locale

Example::dump($default->duration(123.454321));
Example::dump($locale->duration(123.4543));
Example::dump($locale->duration(1_234_543));
```

The `$default->duration(123.454321)` is:

```plain
"00:02:03.454"
```

The `$locale->duration(123.4543)` is:

```plain
"00:02:03.454"
```

The `$locale->duration(1234543)` is:

```plain
"342:55:43.000"
```

[//]: # (end: preprocess/1bbaf6764d0f3cce)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)
