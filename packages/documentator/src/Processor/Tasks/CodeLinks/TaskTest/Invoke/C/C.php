<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\C;

/**
 * @internal
 * @deprecated for tests
 */
class C {
    protected const C = 'C';

    public function __construct(
        public string $property,
    ) {
        // empty
    }

    public function method(): string {
        return self::C;
    }
}
