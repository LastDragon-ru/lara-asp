<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\GetLinkTokenTarget;

/**
 * @internal
 */
final class TestClass {
    public const Constant = 123;
    /**
     * @deprecated for test
     */
    protected const ConstantDeprecated = 123;

    public string $property;

    /**
     * @deprecated for test
     */
    private int $propertyDeprecated = 123;

    public function __construct(
        public string $promoted,
        /**
         * @deprecated for test
         */
        public string $promotedDeprecated,
    ) {
        $this->property = (string) $this->propertyDeprecated;
    }

    public static function method(): string {
        return __FUNCTION__;
    }

    /**
     * @deprecated for test
     */
    public function methodDeprecated(): string {
        return __FUNCTION__;
    }
}
