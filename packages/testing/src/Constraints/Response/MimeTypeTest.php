<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Response\MimeType
 */
class MimeTypeTest extends TestCase {
    /**
     * @covers ::evaluate
     */
    public function testEvaluateInvalidArgument(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new MimeType(''))->evaluate(new stdClass()));
    }

    /**
     * @covers ::evaluate
     */
    public function testEvaluate(): void {
        $map        = [
            'example/text'    => ['example'],
            'example/example' => ['example'],
        ];
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $valid2     = (new Response())->withHeader('Content-Type', 'example/text;charset=utf-8');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $constraint = new MimeType('example', $map);

        self::assertTrue($constraint->evaluate($valid, '', true));
        self::assertTrue($constraint->evaluate($valid2, '', true));
        self::assertFalse($constraint->evaluate($invalid, '', true));
    }

    /**
     * @covers ::toString
     */
    public function testToString(): void {
        $constraint = new MimeType('example', [
            'example/text'    => ['example'],
            'example/example' => ['example'],
        ]);

        self::assertEquals(
            'has Content-Type header that is equal to \'example/text\' or starts with "example/text;" or '.
            'has Content-Type header that is equal to \'example/example\' or starts with "example/example;"',
            $constraint->toString(),
        );
    }
}
