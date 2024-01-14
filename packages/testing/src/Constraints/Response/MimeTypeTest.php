<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(MimeType::class)]
final class MimeTypeTest extends TestCase {
    public function testEvaluateInvalidArgument(): void {
        self::expectExceptionObject(new InvalidArgumentResponse('$response', new stdClass()));

        self::assertFalse((new MimeType(''))->evaluate(new stdClass()));
    }

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
