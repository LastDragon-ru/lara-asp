<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpClass::class)]
final class PhpClassTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNotNull($metadata);
    }

    public function testInvokeNotPhp(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile('../../../README.md');
        $factory  = new PhpClass();
        $metadata = $factory($file);

        self::assertNull($metadata);
    }
}
