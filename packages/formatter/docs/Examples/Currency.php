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
