<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait DatabaseAssertions {
    /**
     * Asserts that SQL Query equals SQL Query.
     *
     * @param Query|QueryBuilder|EloquentBuilder<Model>|array{query: string, bindings: array<mixed>}|string $expected
     * @param Query|QueryBuilder|EloquentBuilder<Model>|array{query: string, bindings: array<mixed>}|string $actual
     */
    public static function assertDatabaseQueryEquals(
        Query|QueryBuilder|EloquentBuilder|array|string $expected,
        Query|QueryBuilder|EloquentBuilder|array|string $actual,
        string $message = '',
    ): void {
        static::assertEquals(Args::getDatabaseQuery($expected), Args::getDatabaseQuery($actual), $message);
    }
}
