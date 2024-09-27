<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Closure;
use Collator;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;

class Sorter {
    public function __construct(
        protected readonly ApplicationResolver $app,
    ) {
        // empty
    }

    /**
     * @return Closure(string, string): int
     */
    public function forString(SortOrder $order): Closure {
        $collator = $this->getCollator();
        $closure  = match ($order) {
            SortOrder::Asc  => static function (string $a, string $b) use ($collator): int {
                return +((int) $collator->compare($a, $b));
            },
            SortOrder::Desc => static function (string $a, string $b) use ($collator): int {
                return -((int) $collator->compare($a, $b));
            },
        };

        return $closure;
    }

    /**
     * @return Closure(string, string): int
     */
    public function forVersion(SortOrder $order): Closure {
        return match ($order) {
            SortOrder::Asc  => static function (string $a, string $b): int {
                return Version::compare($a, $b);
            },
            SortOrder::Desc => static function (string $a, string $b): int {
                return Version::compare($b, $a);
            },
        };
    }

    private function getCollator(): Collator {
        $collator = Collator::create($this->app->getInstance()->getLocale()) ?? new Collator('root');
        $collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);

        return $collator;
    }
}
