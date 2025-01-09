<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\A;

/**
 * @internal
 */
class A {
    protected const string A = 'AA';

    public function __construct(
        public string $property,
    ) {
        // empty
    }

    /**
     * @deprecated for test
     */
    public function method(): string {
        return self::A;
    }
}
