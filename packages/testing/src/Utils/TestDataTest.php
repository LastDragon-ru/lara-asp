<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function str_replace;

/**
 * @internal
 */
#[CoversClass(TestData::class)]
final class TestDataTest extends TestCase {
    public function testPath(): void {
        $data = new TestData($this::class);
        $file = str_replace('\\', '/', dirname(__FILE__).'/TestDataTest');

        self::assertSame("{$file}/", $data->path(''));
        self::assertSame("{$file}.php", $data->path('.php'));
        self::assertSame("{$file}.file.php", $data->path('.file.php'));
        self::assertSame("{$file}~.php", $data->path('~.php'));
        self::assertSame("{$file}~file.php", $data->path('~file.php'));
        self::assertSame("{$file}/php", $data->path('php'));
        self::assertSame("{$file}/file.php", $data->path('file.php'));
        self::assertSame("{$file}/path/to/file.php", $data->path('path/to/file.php'));
        self::assertSame("{$file}/./path/to/file.php", $data->path('./path/to/file.php'));
        self::assertSame("{$file}/../path/to/file.php", $data->path('../path/to/file.php'));
    }
}
