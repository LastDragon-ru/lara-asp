<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(StatusCode::class)]
final class StatusCodeTest extends TestCase {
    public function testEvaluateInvalidArgument(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new StatusCode(200))->evaluate(new stdClass()));
    }

    public function testEvaluate(): void {
        $valid      = new Response(200);
        $invalid    = new Response(500);
        $constraint = new StatusCode(200);

        self::assertTrue($constraint->evaluate($valid, '', true));
        self::assertFalse($constraint->evaluate($invalid, '', true));
    }

    public function testToString(): void {
        $constraint = new StatusCode(200);

        self::assertSame('has Status Code is equal to 200', $constraint->toString());
    }
}
