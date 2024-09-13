<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\B;

/**
 * @internal
 */
class B {
    /**
     * @deprecated for test
     */
    protected const B = 'B';

    public function __construct(
        public string $property,
    ) {
        // empty
    }

    public function method(): string {
        return self::B;
    }
}
