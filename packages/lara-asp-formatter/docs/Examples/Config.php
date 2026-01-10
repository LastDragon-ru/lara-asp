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
