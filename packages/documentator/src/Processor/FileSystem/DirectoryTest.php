<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase {
    use WithProcessor;

    public function testConstruct(): void {
        $path    = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('isDirectory')
            ->once()
            ->andReturn(true);

        $directory = new Directory($adapter, $path);

        self::assertSame($path->getName(), $directory->getName());
    }

    public function testConstructNotDirectory(): void {
        $path    = (new DirectoryPath(__FILE__))->getNormalizedPath();
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('isDirectory')
            ->once()
            ->andReturn(false);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a directory.', $path));

        new Directory($adapter, $path);
    }

    public function testIsInside(): void {
        $a         = (new FilePath(self::getTestData()->path('a/a.txt')));
        $b         = $a->getPath(new DirectoryPath('../../..'));
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(__FILE__);
        $directory = $fs->getDirectory(__DIR__);

        self::assertTrue($directory->isInside($a));
        self::assertFalse($directory->isInside($b));
        self::assertTrue($directory->isInside($file));
        self::assertFalse($directory->isInside($directory));
    }
}
