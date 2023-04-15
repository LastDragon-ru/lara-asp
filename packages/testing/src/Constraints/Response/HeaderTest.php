<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Header::class)]
class HeaderTest extends TestCase {
    public function testEvaluateInvalidArgument(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new Header('Test'))->evaluate(new stdClass()));
    }

    public function testEvaluate(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $invalid2   = new Response();
        $constraint = new Header('Content-Type');

        self::assertTrue($constraint->evaluate($valid, '', true));
        self::assertTrue($constraint->evaluate($invalid, '', true));
        self::assertFalse($constraint->evaluate($invalid2, '', true));
    }

    public function testEvaluateConstraints(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $invalid2   = new Response();
        $constraint = new Header('Content-Type', [new IsEqual('example/text')]);

        self::assertTrue($constraint->evaluate($valid, '', true));
        self::assertFalse($constraint->evaluate($invalid, '', true));
        self::assertFalse($constraint->evaluate($invalid2, '', true));
    }

    public function testToString(): void {
        $constraint = new Header('Content-Type');

        self::assertEquals('has Content-Type header', $constraint->toString());
    }

    public function testToStringConstraints(): void {
        $constraint = new Header('Content-Type', [new IsEqual('example/text')]);

        self::assertEquals("has Content-Type header that is equal to 'example/text'", $constraint->toString());
    }
}
