<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use Illuminate\Http\Response;
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/The `[^`]+` must be instance of `[^`]+`./');

        (new ContentType(''))->evaluate(new stdClass());
    }

    /**
     * @covers ::matches
     */
    public function testMatches(): void {
        $valid      = (new Response())->header('Content-Type', 'example/text');
        $invalid    = (new Response())->header('Content-Type', 'example/invalid');
        $constraint = new class('example/text') extends ContentType {
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
        $constraint = new ContentType('example/text');

        $this->assertEquals('Content-Type is example/text', $constraint->toString());
    }
}
