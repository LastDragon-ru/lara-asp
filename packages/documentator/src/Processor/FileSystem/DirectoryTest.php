<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase {
    use WithProcessor;

    public function testIsInside(): void {
        $a         = (new FilePath(self::getTestData()->path('a/a.txt')));
        $b         = $a->getPath(new DirectoryPath('../../..'));
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(new FilePath(__FILE__));
        $directory = $fs->getDirectory(new DirectoryPath(__DIR__));

        self::assertTrue($directory->isInside($a));
        self::assertFalse($directory->isInside($b));
        self::assertTrue($directory->isInside($file));
        self::assertFalse($directory->isInside($directory));
    }
}
