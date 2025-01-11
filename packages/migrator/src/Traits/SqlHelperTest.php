<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Traits;

use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(SqlHelper::class)]
final class SqlHelperTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<array-key, ?string> $types
     */
    #[DataProvider('dataProviderGetSqlPath')]
    public function testGetSqlPath(string $expected, string $path, array $types): void {
        $instance = new class() {
            use SqlHelper {
                getSqlPath as public;
            }
        };

        self::assertSame($expected, $instance->getSqlPath($path, ...$types));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string, array<array-key, ?string>}>
     */
    public static function dataProviderGetSqlPath(): array {
        return [
            'sql'             => [
                'path/to/file.sql',
                'path/to/file.sql',
                [],
            ],
            'sql with types'  => [
                'path/to/file.sql',
                'path/to/file.sql',
                ['a', 'b', 'c'],
            ],
            'file'            => [
                'path/to/file.sql',
                'path/to/file.txt',
                [],
            ],
            'file with type'  => [
                'path/to/file~a.sql',
                'path/to/file.txt',
                ['a'],
            ],
            'file with types' => [
                'path/to/file~a.b.c.sql',
                'path/to/file.txt',
                ['a', '', 'b', null, 'c'],
            ],
        ];
    }
    // </editor-fold>
}
