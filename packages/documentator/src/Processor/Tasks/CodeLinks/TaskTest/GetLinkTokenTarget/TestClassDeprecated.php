<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\GetLinkTokenTarget;

/**
 * @deprecated for test
 * @internal
 */
final class TestClassDeprecated {
    public const Constant = 123;

    public string $property;

    public function __construct(
        public string $promoted,
    ) {
        $this->property = '123';
    }

    public static function method(): string {
        return __FUNCTION__;
    }
}
