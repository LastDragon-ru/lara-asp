<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Content::class)]
final class ContentTest extends TestCase {
    public function testChanged(): void {
        $content = new Content();
        $file    = Mockery::mock(File::class);

        self::assertFalse($content->changed($file));

        $content[$file] = 'abc';

        self::assertFalse($content->changed($file));

        $content[$file] = 'abc';

        self::assertFalse($content->changed($file));

        $content[$file] = 'cba';

        self::assertTrue($content->changed($file));

        unset($content[$file]);

        self::assertFalse($content->changed($file));
    }

    public function testChanges(): void {
        $aFile   = Mockery::mock(File::class);
        $bFile   = Mockery::mock(File::class);
        $content = new Content();

        $content[$aFile] = 'a';
        $content[$bFile] = 'b';

        self::assertSame([], $content->changes());

        $content[$aFile] = 'aa';
        $content[$bFile] = 'bb';

        self::assertSame([$aFile, $bFile], $content->changes());
        self::assertSame([$aFile, $bFile], $content->changes());
    }

    public function testDelete(): void {
        $fs      = Mockery::mock(FileSystem::class);
        $aFile   = new File($fs, new FilePath('/directory/a.txt'));
        $bFile   = new File($fs, new FilePath('/directory/b.txt'));
        $cFile   = new File($fs, new FilePath('/c.txt'));
        $content = new Content();

        $content[$aFile] = 'a';
        $content[$bFile] = 'b';
        $content[$cFile] = 'c';

        self::assertTrue(isset($content[$aFile]));
        self::assertTrue(isset($content[$bFile]));
        self::assertTrue(isset($content[$cFile]));

        $content->delete($cFile->path);

        self::assertTrue(isset($content[$aFile]));
        self::assertTrue(isset($content[$bFile]));
        self::assertFalse(isset($content[$cFile]));

        $content->delete($aFile->path->directory());

        self::assertFalse(isset($content[$aFile]));
        self::assertFalse(isset($content[$bFile]));
        self::assertFalse(isset($content[$cFile]));
    }

    public function testReset(): void {
        $file    = Mockery::mock(File::class);
        $content = new Content();

        $content[$file] = 'a';
        $content[$file] = 'b';

        self::assertTrue($content->changed($file));

        $content->reset($file);

        self::assertFalse($content->changed($file));
        self::assertTrue(isset($content[$file]));
    }

    public function testArrayAccess(): void {
        $content = new Content();
        $file    = Mockery::mock(File::class);

        self::assertFalse(isset($content[$file]));
        self::assertNull($content[$file]);

        $content[$file] = 'abc';

        self::assertTrue(isset($content[$file]));
        self::assertSame('abc', $content[$file]);

        unset($content[$file]);

        self::assertFalse(isset($content[$file]));
        self::assertNull($content[$file]);
    }
}
