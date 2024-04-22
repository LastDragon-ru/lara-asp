<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Formatter\Formatter;

$default = Example::app()->make(Formatter::class); // For default app locale
$locale  = $default->forLocale('ru_RU');           // For ru_RU locale

Example::dump($default->duration(123.454321));
Example::dump($locale->duration(123.4543));
Example::dump($locale->duration(1_234_543));
