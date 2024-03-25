<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Package;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

use function trigger_deprecation;

/**
 * @mixin Assert
 */
trait ResponseAssertions {
    /**
     * Asserts that PSR Response satisfies given constraint.
     *
     * @deprecated 6.0.0 Please use {@see static::assertPsrResponse()}
     */
    public static function assertThatResponse(
        ResponseInterface $response,
        Constraint $constraint,
        string $message = '',
    ): void {
        trigger_deprecation(Package::Name, '6.0.0', 'Please use `static::assertPsrResponse()` instead.');

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
