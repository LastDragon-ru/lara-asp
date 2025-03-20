<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use const PHP_INT_MIN;

readonly class Prepend extends Location {
    public function __construct() {
        parent::__construct(PHP_INT_MIN, PHP_INT_MIN);
    }
}
