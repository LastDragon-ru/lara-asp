<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Http;

interface ValueProvider {
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getValue($value);
}
