<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin Assert
 */
trait ResponseAssertions {
    /**
     * Asserts that PSR Response satisfies given constraint.
     */
    public static function assertThatResponse(
        ResponseInterface $response,
        Constraint $constraint,
        string $message = '',
    ): void {
        static::assertThat($response, $constraint, $message);
    }

    /**
     * Asserts that PSR Response satisfies given constraint.
     */
    public static function assertPsrResponse(
        Response $expected,
        ResponseInterface $actual,
        string $message = '',
    ): void {
        static::assertThat($actual, $expected, $message);
    }
}
