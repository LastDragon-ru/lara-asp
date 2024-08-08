<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpClass::class)]
final class PhpClassTest extends TestCase {
    public function testInvoke(): void {
        $file     = new File(Path::normalize(__FILE__), false);
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNotNull($metadata);
    }

    public function testInvokeNotPhp(): void {
        $file     = new File(Path::getPath(__FILE__, '../../../README.md'), false);
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNull($metadata);
    }
}
