<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use InvalidArgumentException;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function mb_trim;

/**
 * @internal
 */
#[CoversClass(TestData::class)]
final class TestDataTest extends TestCase {
    public function testGet(): void {
        self::assertSame(TestData::get(), TestData::get());
    }

    public static function testGetStatic(): void {
        self::assertNotSame(TestData::get(), TestData::get());
    }

    public function testFile(): void {
        $root = new DirectoryPath(__DIR__);

        self::assertSame('TestDataTest/a.txt', (string) $root->relative(TestData::get()->file('a.txt')));
        self::assertSame('TestDataTest/a/a.txt', (string) $root->relative(TestData::get()->file('a/a.txt')));
    }

    public function testFileOutside(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches('#^Path `[^`]+?` must be inside `[^`]+?`\.$#');

        TestData::get()->file('../a.txt');
    }

    public function testDirectory(): void {
        $root = new DirectoryPath(__DIR__);

        self::assertSame('TestDataTest/', (string) $root->relative(TestData::get()->directory()));
        self::assertSame('TestDataTest/a/', (string) $root->relative(TestData::get()->directory('a')));
        self::assertSame('TestDataTest/a/a/', (string) $root->relative(TestData::get()->directory('a/a')));
    }

    public function testDirectoryOutside(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches('#^Path `[^`]+?` must be inside `[^`]+?`\.$#');

        TestData::get()->directory('../a');
    }

    public function testContent(): void {
        self::assertSame('content', mb_trim(TestData::get()->content('content.txt')));
    }
}
