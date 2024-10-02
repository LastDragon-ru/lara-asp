<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;
use stdClass;

/**
 * @internal
 */
#[CoversClass(DatabaseQueryComparator::class)]
final class DatabaseQueryComparatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderAccepts')]
    public function testAccepts(bool $equals, mixed $expected, mixed $actual): void {
        self::assertEquals($equals, (new DatabaseQueryComparator())->accepts($expected, $actual));
    }

    #[DataProvider('dataProviderAssertEquals')]
    public function testAssertEquals(bool|string $equals, mixed $expected, mixed $actual): void {
        if ($equals !== true) {
            self::expectException(ComparisonFailure::class);
            self::expectExceptionMessageMatches(
                $equals !== false ? $equals : '/Failed asserting that two database queries are equal/i',
            );
        }

        $comparator = new DatabaseQueryComparator();

        $comparator->setFactory(Factory::getInstance());
        $comparator->assertEquals($expected, $actual);

        self::assertTrue($equals);
    }

    public function testNormalize(): void {
        $comparator = new class() extends DatabaseQueryComparator {
            #[Override]
            public function normalize(Query $query): Query {
                return parent::normalize($query);
            }
        };
        $query      = new Query(
            <<<'SQL'
            SELECT laravel_reserved_1.a, laravel_reserved_10.b, laravel_reserved_2.c
            FROM a laravel_reserved_1
            INNER JOIN b laravel_reserved_10 ON laravel_reserved_10.b = laravel_reserved_2.c
            INNER JOIN c laravel_reserved_2 ON laravel_reserved_2.c = laravel_reserved_1.a
            WHERE laravel_reserved_1.a IS NOT NULL
                AND laravel_reserved_10.b IS NULL
                AND laravel_reserved_2.c > 10
            SQL,
        );
        $expected   = <<<'SQL'
            SELECT
                laravel_reserved_0.a,
                laravel_reserved_2.b,
                laravel_reserved_1.c
            FROM
                a laravel_reserved_0
                INNER JOIN b laravel_reserved_2 ON laravel_reserved_2.b = laravel_reserved_1.c
                INNER JOIN c laravel_reserved_1 ON laravel_reserved_1.c = laravel_reserved_0.a
            WHERE
                laravel_reserved_0.a IS NOT NULL
                AND laravel_reserved_2.b IS NULL
                AND laravel_reserved_1.c > 10
            SQL;

        self::assertEquals($expected, $comparator->normalize($query)->getQuery());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderAccepts(): array {
        return [
            'query + query'  => [
                true,
                new class('') extends Query {
                    // empty
                },
                new class('') extends Query {
                    // empty
                },
            ],
            'query + object' => [
                false,
                new class('') extends Query {
                    // empty
                },
                new stdClass(),
            ],
            'query + scalar' => [
                false,
                new class('') extends Query {
                    // empty
                },
                1,
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderAssertEquals(): array {
        $a = new Query(
            <<<'SQL'
            SELECT *
            FROM `a`
            WHERE a.id = ?
            SQL,
            [123],
        );
        $b = new Query('SELECT * FROM `a` WHERE a.id = ?', [345]);
        $c = new Query('SELECT * FROM `b`');

        return [
            'different classes'  => [
                '/.+? is not instance of expected class ".+?"/i',
                new class('') extends Query {
                    // empty
                },
                new class('') extends Query {
                    // empty
                },
            ],
            'same query'         => [
                true,
                $a,
                $a,
            ],
            'different query'    => [
                false,
                $a,
                $c,
            ],
            'different bindings' => [
                false,
                $a,
                $b,
            ],
        ];
    }
    // </editor-fold>
}
