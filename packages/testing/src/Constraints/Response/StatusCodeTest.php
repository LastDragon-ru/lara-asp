<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(StatusCode::class)]
class StatusCodeTest extends TestCase {
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

        self::assertEquals('has Status Code is equal to 200', $constraint->toString());
    }
}
