<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode
 */
class StatusCodeTest extends TestCase {
    /**
     * @covers ::evaluate
     */
    public function testEvaluate(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new StatusCode(200))->evaluate(new stdClass()));
    }

    /**
     * @covers ::matches
     */
    public function testMatches(): void {
        $valid      = new Response(200);
        $invalid    = new Response(500);
        $constraint = new class(200) extends StatusCode {
            /**
             * @inheritdoc
             */
            public function matches($other): bool {
                return parent::matches($other);
            }
        };

        self::assertTrue($constraint->matches($valid));
        self::assertFalse($constraint->matches($invalid));
    }

    /**
     * @covers ::toString
     */
    public function testToString(): void {
        $constraint = new StatusCode(200);

        self::assertEquals('has Status Code is equal to 200', $constraint->toString());
    }
}
