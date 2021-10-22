<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query as DatabaseQuery;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait DatabaseAssertions {
    /**
     * Asserts that SQL equals SQL.
     *
     * @param DatabaseQuery|QueryBuilder|EloquentBuilder|array{query: string, bindings: array<mixed>}|string $expected
     * @param DatabaseQuery|QueryBuilder|EloquentBuilder|array{query: string, bindings: array<mixed>}|string $actual
     */
    public static function assertDatabaseQueryEquals(
        DatabaseQuery|QueryBuilder|EloquentBuilder|array|string $expected,
        DatabaseQuery|QueryBuilder|EloquentBuilder|array|string $actual,
        string $message = '',
    ): void {
        static::assertEquals(Args::getDatabaseQuery($expected), Args::getDatabaseQuery($actual), $message);
    }
}
