<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use const PHP_INT_MAX;

readonly class Append extends Location {
    public function __construct() {
        parent::__construct(PHP_INT_MAX, PHP_INT_MAX);
    }
}
