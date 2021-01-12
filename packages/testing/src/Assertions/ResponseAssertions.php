<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait ResponseAssertions {
    /**
     * Asserts that PSR Response satisfies given constraint.
     *
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \PHPUnit\Framework\Constraint\Constraint $constraint
     * @param string                                   $message
     *
     * @return void
     */
    public static function assertThatResponse(ResponseInterface $response, Constraint $constraint, string $message = ''): void {
        static::assertThat($response, $constraint, $message);
    }
}
