<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts;

use Closure;

interface LinkFactory {
    /**
     * @param Closure(string): (string|null)|null $resolver
     */
    public function create(string $string, ?Closure $resolver = null): ?Link;
}
