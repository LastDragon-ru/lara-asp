<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Response\Header
 */
class HeaderTest extends TestCase {
    /**
     * @covers ::evaluate
     */
    public function testEvaluate(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new Header('Test'))->evaluate(new stdClass()));
    }

    /**
     * @covers ::matches
     */
    public function testMatches(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $constraint = new class('Content-Type') extends Header {
            /**
             * @inheritdoc
             */
            public function matches($other): bool {
                return parent::matches($other);
            }
        };

        self::assertTrue($constraint->matches($valid));
        self::assertTrue($constraint->matches($invalid));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesConstraints(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $constraint = new class('Content-Type', [new IsEqual('example/text')]) extends Header {
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
        $constraint = new Header('Content-Type');

        self::assertEquals('has Content-Type header', $constraint->toString());
    }

    /**
     * @covers ::toString
     */
    public function testToStringConstraints(): void {
        $constraint = new Header('Content-Type', [new IsEqual('example/text')]);

        self::assertEquals("has Content-Type header that is equal to 'example/text'", $constraint->toString());
    }
}
