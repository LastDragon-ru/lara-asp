<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Content::class)]
final class ContextTest extends TestCase {
    public function testResolve(): void {
        $file     = Mockery::mock(File::class);
        $context  = [$this, new stdClass()];
        $metadata = new Context($context);

        self::assertTrue($metadata->isSupported(TestCase::class));
        self::assertSame($context[0], $metadata->resolve($file, TestCase::class));
        self::assertTrue($metadata->isSupported(stdClass::class));
        self::assertSame($context[1], $metadata->resolve($file, stdClass::class));
    }

    public function testResolveUnknown(): void {
        $file     = Mockery::mock(File::class);
        $context  = [];
        $metadata = new Context($context);

        self::assertFalse($metadata->isSupported(stdClass::class));

        self::expectException(OutOfBoundsException::class);
        self::expectExceptionMessage('The `stdClass` not found in `$context`.');

        $metadata->resolve($file, stdClass::class);
    }
}
