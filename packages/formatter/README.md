# (Laravel) Intl Formatter

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters to use it inside Laravel application.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: 3556073e7992c5bd81cdd63a92c38d136657c7e720caec135fff44e925557f7b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
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

[//]: # (end: 3556073e7992c5bd81cdd63a92c38d136657c7e720caec135fff44e925557f7b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "formatter"}})
[//]: # (start: ca18ec42d5b2c99e52f3a550acc6e29f65958871ab3405d38e82ef8eab2ad415)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-formatter
```

[//]: # (end: ca18ec42d5b2c99e52f3a550acc6e29f65958871ab3405d38e82ef8eab2ad415)

# Usage

Formatter is very simple to use:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: adef85def212f87b8f157b40eaa0695da87e1a6f63e1e1ebc9fff474ecad3fee)
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

[//]: # (end: adef85def212f87b8f157b40eaa0695da87e1a6f63e1e1ebc9fff474ecad3fee)

Please check [source code](./src/Formatter.php) to see available methods and [config example](defaults/config.php) to available settings 🤗

# Formats

Some methods like as `date()`/`datetime()`/etc have `$format` argument. The argument specifies not the format but the format name. So you can use the names and do not worry about real formats. It is very important when application big/grow. To specify available names and formats the package config should be published and customized.

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Formatter\\Provider --tag=config
```

[include:example]: ./docs/Examples/Config.php
[//]: # (start: 79abdc1016ae295c77f37d2c430cec8b2de87be7096c32913e441a5d5057ec31)
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

[//]: # (end: 79abdc1016ae295c77f37d2c430cec8b2de87be7096c32913e441a5d5057ec31)

# Duration

To format duration you can use built-in Intl formatter, but it doesn't support fraction seconds and have different format between locales (for example, `12345` seconds is `3:25:45` in `en_US` locale, and `12 345` in `ru_RU`). These reasons make difficult to use it in real applications. To make `duration()` more useful, the alternative syntax was added.

[include:docblock]: ./src/Utils/DurationFormatter.php ({"summary": false})
[//]: # (start: 363cfceaffb54119c82e514732db74b5265a5fc6724699580b2d3c677c1258f7)
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

[//]: # (end: 363cfceaffb54119c82e514732db74b5265a5fc6724699580b2d3c677c1258f7)

[include:example]: ./docs/Examples/Duration.php
[//]: # (start: 4601baed9dde5929ecc10d49ca83c24f5f9b1394fefd9f200fbc3085d5469cf1)
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

[//]: # (end: 4601baed9dde5929ecc10d49ca83c24f5f9b1394fefd9f200fbc3085d5469cf1)

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
