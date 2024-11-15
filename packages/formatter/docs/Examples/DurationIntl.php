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
