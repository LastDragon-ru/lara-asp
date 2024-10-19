<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpClass::class)]
final class PhpClassTest extends TestCase {
    public function testInvoke(): void {
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNotNull($metadata);
    }

    public function testInvokeNotPhp(): void {
        $file     = new File((new FilePath(__FILE__))->getFilePath('../../../README.md'), false);
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNull($metadata);
    }
}
