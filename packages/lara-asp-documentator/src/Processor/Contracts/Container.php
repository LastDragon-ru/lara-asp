<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

interface Container {
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function make(string $class): object;
}
