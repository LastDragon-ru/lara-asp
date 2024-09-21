<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke;

/**
 * @internal
 */
class A {
    protected const A = 'A';

    public function __construct(
        public string $property,
    ) {
        // empty
    }

    public function method(): string {
        return self::A;
    }
}
