<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Assertions\JsonAssertions;
use LastDragon_ru\LaraASP\Testing\Assertions\XmlAssertions;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Factory;
use PHPUnit\Framework\Assert as PHPUnitAssert;
use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class Assert extends PHPUnitAssert {
    use XmlAssertions;
    use JsonAssertions;

    /**
     * Asserts that TestResponse satisfies given constraint.
     *
     * @template T of Response
     *
     * @param TestResponse<T> $response
     */
    public static function assertThatResponse(
        TestResponse $response,
        Constraint $constraint,
        string $message = '',
    ): void {
        static::assertThat(Factory::make($response), $constraint, $message);
    }
}
