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
