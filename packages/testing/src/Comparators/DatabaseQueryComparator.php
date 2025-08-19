<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query;
use Override;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use stdClass;

use function array_column;
use function array_flip;
use function array_unique;
use function array_values;
use function mb_strlen;
use function natsort;
use function preg_match_all;
use function str_replace;
use function uksort;

use const PREG_SET_ORDER;

/**
 * Compares two {@link Query}.
 *
 * We are performing following normalization before comparison to be more precise:
 *
 * * Renumber `laravel_reserved_*` (it will always start from `0` and will not contain gaps)
 * * Format the query by [`doctrine/sql-formatter`](https://github.com/doctrine/sql-formatter) package
 */
class DatabaseQueryComparator extends Comparator {
    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool {
        return $expected instanceof Query
            && $actual instanceof Query;
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        // Comparator
        $comparator = $this->factory()->getComparatorFor(new stdClass(), new stdClass());

        // If classes different we just call parent to fail
        if (!($actual instanceof Query) || !($expected instanceof Query) || $actual::class !== $expected::class) {
            $comparator->assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);

            return;
        }

        // Normalize queries and compare
        $normalizedExpected = $this->normalize($expected);
        $normalizedActual   = $this->normalize($actual);

        try {
            $comparator->assertEquals($normalizedExpected, $normalizedActual, $delta, $canonicalize, $ignoreCase);
        } catch (ComparisonFailure $exception) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $exception->getExpectedAsString(),
                $exception->getActualAsString(),
                'Failed asserting that two database queries are equal.',
            );
        }
    }

    protected function normalize(Query $query): Query {
        // Prepare
        $class    = $query::class;
        $sql      = $query->getQuery();
        $bindings = $query->getBindings();

        // Laravel's aliases have a global counter and are dependent on tests
        // execution order -> we need to normalize them before comparison.
        if (preg_match_all('/(?<group>laravel_reserved_[\d]+)/', $sql, $matches, PREG_SET_ORDER) > 0) {
            $matches = array_unique(array_column($matches, 'group'));

            natsort($matches);

            $matches = array_values($matches);
            $matches = array_flip($matches);

            uksort($matches, static function (string|int $a, string|int $b): int {
                return mb_strlen("{$b}") <=> mb_strlen("{$a}");
            });

            foreach ($matches as $match => $index) {
                $sql = str_replace($match, "__tmp_alias_{$index}", $sql);
            }

            $sql = str_replace('__tmp_alias_', 'laravel_reserved_', $sql);
        }

        // Format
        $sql = (new SqlFormatter(new NullHighlighter()))->format($sql, '    ');

        // Return
        return new $class($sql, $bindings);
    }
}
