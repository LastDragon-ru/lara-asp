<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType
 */
class ContentTypeTest extends TestCase {
    public function testEvaluateInvalidArgument(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new ContentType(''))->evaluate(new stdClass()));
    }

    public function testEvaluate(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $valid2     = (new Response())->withHeader('Content-Type', 'example/text;charset=utf-8');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $constraint = new ContentType('example/text');

        self::assertTrue($constraint->evaluate($valid, '', true));
        self::assertTrue($constraint->evaluate($valid2, '', true));
        self::assertFalse($constraint->evaluate($invalid, '', true));
    }

    public function testToString(): void {
        $constraint = new ContentType('example/text');

        self::assertEquals(
            'has Content-Type header that is equal to \'example/text\' or starts with "example/text;"',
            $constraint->toString(),
        );
    }
}
