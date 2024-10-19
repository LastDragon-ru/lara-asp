<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_shift;
use function file_get_contents;
use function file_put_contents;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase {
    public function testConstruct(): void {
        $path = (new FilePath(__FILE__))->getNormalizedPath();
        $file = new File($path, false);

        self::assertEquals($path, $file->getPath());
        self::assertEquals("{$path}", (string) $file->getPath());
        self::assertEquals('php', $file->getExtension());
        self::assertEquals('FileTest.php', $file->getName());
    }

    public function testConstructNotNormalized(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be normalized, `/../file.txt` given.');

        new File(new FilePath('/../file.txt'), false);
    }

    public function testConstructNotAbsolute(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Path must be absolute, `../file.txt` given.');

        new File(new FilePath('../file.txt'), false);
    }

    public function testConstructNotFile(): void {
        $path = (new FilePath(__DIR__))->getNormalizedPath();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('The `%s` is not a file.', $path));

        new File($path, false);
    }

    public function testGetContent(): void {
        $temp = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file = new File($temp, false);
        $path = (string) $file;

        self::assertEquals(__FILE__, $file->getContent());
        self::assertNotFalse(file_put_contents($path, __DIR__));
        self::assertEquals(__DIR__, file_get_contents($path));
        self::assertEquals(__FILE__, $file->getContent());
    }

    public function testSetContent(): void {
        $temp    = (new FilePath(self::getTempFile(__FILE__)->getPathname()))->getNormalizedPath();
        $file    = new File($temp, false);
        $path    = (string) $file;
        $meta    = new class([1, 2]) implements Metadata {
            public function __construct(
                /**
                 * @var list<int>
                 */
                private array $value,
            ) {
                // empty
            }

            #[Override]
            public function __invoke(File $file): mixed {
                return array_shift($this->value);
            }
        };
        $current = $file->getMetadata($meta);

        self::assertEquals(__FILE__, $file->getContent());
        self::assertSame($current, $file->getMetadata($meta));
        self::assertNotFalse(file_put_contents($path, __DIR__));
        self::assertSame($file, $file->setContent(__METHOD__));
        self::assertEquals(__DIR__, file_get_contents($path));
        self::assertEquals(__METHOD__, $file->getContent());
        self::assertNotEquals($current, $file->getMetadata($meta));
    }

    public function testGetRelativePath(): void {
        $path      = new FilePath('a/a.txt');
        $file      = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $parent    = new Directory($file->getPath()->getParentPath()->getParentPath(), false);
        $directory = new Directory((new DirectoryPath(__DIR__))->getNormalizedPath(), true);

        self::assertEquals('..', (string) $file->getRelativePath($parent));
        self::assertEquals('FileTest.php', (string) $file->getRelativePath($file));
        self::assertEquals('a/a.txt', (string) $file->getRelativePath($path));
        self::assertEquals('', (string) $file->getRelativePath($directory));
    }
}
