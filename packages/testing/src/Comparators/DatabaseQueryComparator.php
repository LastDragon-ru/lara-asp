<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ObjectComparator;

use function array_column;
use function array_flip;
use function array_unique;
use function array_values;
use function natsort;
use function preg_match_all;
use function str_replace;
use function strlen;
use function uksort;

use const PREG_SET_ORDER;

/**
 * Compares two {@link Query}.
 */
class DatabaseQueryComparator extends ObjectComparator {
    /**
     * @inheritdoc
     */
    public function accepts($expected, $actual) {
        return $expected instanceof Query
            && $actual instanceof Query;
    }

    /**
     * @inheritdoc
     *
     * @param array<mixed> $processed
     */
    public function assertEquals(
        $expected,
        $actual,
        $delta = 0.0,
        $canonicalize = false,
        $ignoreCase = false,
        array &$processed = [],
    ): void {
        // If classes different we just call parent to fail
        if (!($actual instanceof Query) || !($expected instanceof Query) || $actual::class !== $expected::class) {
            parent::assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase, $processed);
        }

        // Normalize queries and compare
        $normalizedExpected = $this->normalize($expected);
        $normalizedActual   = $this->normalize($actual);

        try {
            parent::assertEquals(
                $normalizedExpected,
                $normalizedActual,
                $delta,
                $canonicalize,
                $ignoreCase,
                $processed,
            );
        } catch (ComparisonFailure $exception) {
            throw new ComparisonFailure(
                $normalizedExpected,
                $normalizedActual,
                $exception->getExpectedAsString(),
                $exception->getActualAsString(),
                false,
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
        if (preg_match_all('/(?<group>laravel_reserved_[\d]+)/', $sql, $matches, PREG_SET_ORDER)) {
            $matches = array_unique(array_column($matches, 'group'));

            natsort($matches);

            $matches = array_values($matches);
            $matches = array_flip($matches);

            uksort($matches, static function (string $a, string $b): int {
                return strlen($b) <=> strlen($a);
            });

            foreach ($matches as $match => $index) {
                $sql = str_replace((string) $match, "__tmp_alias_{$index}", $sql);
            }

            $sql = str_replace('__tmp_alias_', 'laravel_reserved_', $sql);
        }

        // Format
        $sql = (new SqlFormatter(new NullHighlighter()))->format($sql, '    ');

        // Return
        return new $class($sql, $bindings);
    }
}
