<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\B;

/**
 * @internal
 */
class A {
    /**
     * @deprecated for test
     */
    protected const string A = 'BA';

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
