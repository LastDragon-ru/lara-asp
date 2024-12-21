<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase {
    public function testConstruct(): void {
        $path      = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $directory = new Directory($path);

        self::assertEquals($path->getName(), $directory->getName());
    }

    public function testConstructNotDirectory(): void {
        $path = (new DirectoryPath(__FILE__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a directory.', $path));

        new Directory($path);
    }

    public function testIsInside(): void {
        $a         = (new FilePath(self::getTestData()->path('a/a.txt')));
        $b         = $a->getPath(new DirectoryPath('../../..'));
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath());
        $directory = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath());

        self::assertTrue($directory->isInside($a));
        self::assertFalse($directory->isInside($b));
        self::assertTrue($directory->isInside($file));
        self::assertFalse($directory->isInside($directory));
    }
}
