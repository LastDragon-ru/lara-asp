# (Laravel) Intl Formatter

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters to use it inside Laravel application. And also allows defining own.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |  `HEAD`   |
|  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `7.1.0 ⋯ 2.0.0`   |
|  | `^8.1` |   `6.4.2 ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^11.0.8` |  `HEAD`   |
|  | `^11.0.0` |   `7.1.0 ⋯ 6.2.0`   |
|  | `^10.34.0` |   `7.1.0 ⋯ 6.2.0`   |
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

# Configuration

Config can be used to customize formats. Before this, you need to publish it via the following command, and then you can edit `config/lara-asp-formatter.php`.

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Formatter\\PackageProvider --tag=config
```

# Usage

> [!NOTE]
>
> The resolved formats are cached, thus run-time changes in the configuration will not be applied. You can `clone` the formatter instance to reset the internal cache.

The [`Formatter`][code-links/9fbde97537a14196] is very simple to use. Please also check [`Formatter`][code-links/9fbde97537a14196] to see built-in formats 🤗

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

You can also define separate setting for each locale:

[include:example]: ./docs/Examples/Config.php
[//]: # (start: preprocess/e30ad70238f2c282)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlDateTime\IntlDateTimeFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlDateTime\IntlDateTimeOptions;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;

Example::config(PackageConfig::class, static function (Config $config): void {
    $config->formats[Formatter::Date] = new Format(
        IntlDateTimeFormat::class,
        new IntlDateTimeOptions(
            dateType: IntlDateFormatter::SHORT,
            timeType: IntlDateFormatter::NONE,
            pattern : 'd MMM yyyy',
        ),
        [
            'ru_RU' => new IntlDateTimeOptions(
                pattern: 'dd.MM.yyyy',
            ),
        ],
    );
});

$datetime = Date::make('2023-12-30T20:41:40.000018+04:00');
$default  = app()->make(Formatter::class);
$locale   = $default->forLocale('ru_RU');

Example::dump($default->date($datetime));
Example::dump($locale->date($datetime));
```

The `$default->date($datetime)` is:

```plain
"30 Dec 2023"
```

The `$locale->date($datetime)` is:

```plain
"30.12.2023"
```

[//]: # (end: preprocess/e30ad70238f2c282)

# Adding new formats

You just need to create a class that implements [`Format`][code-links/f729e209367a8080], add into the package config, and add macros to the [`Formatter`][code-links/9fbde97537a14196] class.

> [!NOTE]
>
> [include:docblock]: ./src/Contracts/Format.php
> [//]: # (start: preprocess/0015746c2d34336b)
> [//]: # (warning: Generated automatically. Do not edit.)
>
> The instance will be created through container with the following additional
> arguments:
>
> * `$formatter`: [`Formatter`][code-links/9fbde97537a14196] - the current formatter instance (can be used to get locale/timezone).
> * `$options` (array) - formatter options defined inside app config (may contain `null`s).
>
> [//]: # (end: preprocess/0015746c2d34336b)
>

[include:example]: ./docs/Examples/Uppercase.php
[//]: # (start: preprocess/20404ebb04e0776f)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Formatter\Docs\Examples\Uppercase;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format as FormatContract;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;
use Override;
use Stringable;

use function mb_strtoupper;

/**
 * @implements FormatContract<null, Stringable|string|null>
 */
class UppercaseFormat implements FormatContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(mixed $value): string {
        return mb_strtoupper((string) $value);
    }
}

Formatter::macro('uppercase', function (Stringable|string|null $value): string {
    return $this->format('uppercase', $value);
});

Example::config(PackageConfig::class, static function (Config $config): void {
    $config->formats['uppercase'] = new Format(
        UppercaseFormat::class,
    );
});

// @phpstan-ignore method.notFound
Example::dump(app()->make(Formatter::class)->uppercase('string'));
```

The `app()->make(Formatter::class)->uppercase('string')` is:

```plain
"STRING"
```

[//]: # (end: preprocess/20404ebb04e0776f)

# Notes about built-in formats

## Currency

By default, the [`Formatter`][code-links/9fbde97537a14196] use locale currency. You can redefine it globally through config, specify for the call, and/or add a macros for another currency.

[include:example]: ./docs/Examples/Currency.php
[//]: # (start: preprocess/579d73db05700cf0)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlCurrencyFormat;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlCurrencyOptions;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;

Example::config(PackageConfig::class, static function (Config $config): void {
    $config->formats[Formatter::Currency] = new Format(
        IntlCurrencyFormat::class,
        new IntlCurrencyOptions(
            currency: 'USD',
        ),
    );
});

Formatter::macro('eur', function (float|int|null $value): string {
    return $this->format(Formatter::Currency, [$value, 'EUR']);
});

$formatter = app()->make(Formatter::class);
$value     = 123.45;

// @phpstan-ignore method.notFound
Example::dump($formatter->eur($value));             // macro
Example::dump($formatter->currency($value));        // locale default
Example::dump($formatter->currency($value, 'EUR')); // as defined
```

The `$formatter->eur($value)` is:

```plain
"€123.45"
```

The `$formatter->currency($value)` is:

```plain
"$123.45"
```

The `$formatter->currency($value, 'EUR')` is:

```plain
"€123.45"
```

[//]: # (end: preprocess/579d73db05700cf0)

## Duration

To format duration you can use built-in Intl formatter, but it doesn't support fraction seconds and have a different format between locales (for example, `12345` seconds is `3:25:45` in `en_US` locale, and `12 345` in `ru_RU`). These reasons make it difficult to use it in real applications. To make `duration()` more useful, the alternative syntax was added and used by default.

[include:docblock]: ./src/Formats/Duration/DurationFormat.php ({"summary": false})
[//]: # (start: preprocess/ef4289839adfe4ca)
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

[//]: # (end: preprocess/ef4289839adfe4ca)

[include:example]: ./docs/Examples/DurationPattern.php
[//]: # (start: preprocess/75de7a9481771185)
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

[//]: # (end: preprocess/75de7a9481771185)

To use Intl Formatter, you need to change the duration format in the config:

[include:example]: ./docs/Examples/DurationIntl.php
[//]: # (start: preprocess/1e573cfe77ba6df3)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use LastDragon_ru\LaraASP\Formatter\Config\Format;
use LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber\IntlDurationFormat;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;

Example::config(PackageConfig::class, static function (Config $config): void {
    $config->formats[Formatter::Duration] = new Format(
        IntlDurationFormat::class,
    );
});

$default = app()->make(Formatter::class); // For default app locale
$locale  = $default->forLocale('ru_RU');  // For ru_RU locale
$value   = 123.4543;

Example::dump($default->duration($value));
Example::dump($locale->duration($value));
```

The `$default->duration($value)` is:

```plain
"2:03"
```

The `$locale->duration($value)` is:

```plain
"123"
```

[//]: # (end: preprocess/1e573cfe77ba6df3)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/f729e209367a8080]: src/Contracts/Format.php
    "\LastDragon_ru\LaraASP\Formatter\Contracts\Format"

[code-links/9fbde97537a14196]: src/Formatter.php
    "\LastDragon_ru\LaraASP\Formatter\Formatter"

[//]: # (end: code-links)
