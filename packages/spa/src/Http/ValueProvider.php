<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

interface ValueProvider {
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getValue($value);
}
