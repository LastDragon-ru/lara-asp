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

        self::assertEquals("{$file}/", $data->path(''));
        self::assertEquals("{$file}.php", $data->path('.php'));
        self::assertEquals("{$file}.file.php", $data->path('.file.php'));
        self::assertEquals("{$file}~.php", $data->path('~.php'));
        self::assertEquals("{$file}~file.php", $data->path('~file.php'));
        self::assertEquals("{$file}/php", $data->path('php'));
        self::assertEquals("{$file}/file.php", $data->path('file.php'));
        self::assertEquals("{$file}/path/to/file.php", $data->path('path/to/file.php'));
        self::assertEquals("{$file}/./path/to/file.php", $data->path('./path/to/file.php'));
        self::assertEquals("{$file}/../path/to/file.php", $data->path('../path/to/file.php'));
    }
}
