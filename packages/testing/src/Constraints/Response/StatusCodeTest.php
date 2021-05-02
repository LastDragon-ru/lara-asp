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
        $this->expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        $this->assertFalse((new StatusCode(200))->evaluate(new stdClass()));
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

        $this->assertTrue($constraint->matches($valid));
        $this->assertFalse($constraint->matches($invalid));
    }

    /**
     * @covers ::toString
     */
    public function testToString(): void {
        $constraint = new StatusCode(200);

        $this->assertEquals('has Status Code is equal to 200', $constraint->toString());
    }
}
