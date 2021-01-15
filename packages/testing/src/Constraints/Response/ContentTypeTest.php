<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType
 */
class ContentTypeTest extends TestCase {
    /**
     * @covers ::evaluate
     */
    public function testEvaluate(): void {
        $this->expectExceptionObject(new InvalidArgumentException(
            'It is not a `Psr\Http\Message\ResponseInterface` instance.'
        ));

        $this->assertFalse((new ContentType(''))->evaluate(new stdClass()));
    }

    /**
     * @covers ::matches
     */
    public function testMatches(): void {
        $valid      = (new Response())->withHeader('Content-Type', 'example/text');
        $valid2     = (new Response())->withHeader('Content-Type', 'example/text;charset=utf-8');
        $invalid    = (new Response())->withHeader('Content-Type', 'example/invalid');
        $constraint = new class('example/text') extends ContentType {
            public function matches($other): bool {
                return parent::matches($other);
            }
        };

        $this->assertTrue($constraint->matches($valid));
        $this->assertTrue($constraint->matches($valid2));
        $this->assertFalse($constraint->matches($invalid));
    }

    /**
     * @covers ::toString
     */
    public function testToString(): void {
        $constraint = new ContentType('example/text');

        $this->assertEquals('has Content-Type header that is equal to \'example/text\' or starts with "example/text;"', $constraint->toString());
    }
}
