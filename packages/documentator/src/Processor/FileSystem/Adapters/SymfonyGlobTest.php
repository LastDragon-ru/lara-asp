<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
#[CoversClass(SymfonyGlob::class)]
final class SymfonyGlobTest extends TestCase {
    public function testMatch(): void {
        // Prepare
        $glob = new SymfonyGlob(['*.txt', '*.md', '**/*.tmp']);

        // Strings
        self::assertFalse($glob->match(new SplFileInfo('$file', '$path', '/file.txt')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', 'file.md')));
        self::assertFalse($glob->match(new SplFileInfo('$file', '$path', 'file.php')));
        self::assertFalse($glob->match(new SplFileInfo('$file', '$path', 'a/file.md')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', 'file.tmp')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', 'a/file.tmp')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', '/a/file.tmp')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', 'a/b/file.tmp')));
        self::assertTrue($glob->match(new SplFileInfo('$file', '$path', '/a/b/file.tmp')));
    }
}
