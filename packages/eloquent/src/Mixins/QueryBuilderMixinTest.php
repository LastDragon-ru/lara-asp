<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Illuminate\Database\Query\Builder;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use Traversable;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Mixins\QueryBuilderMixin
 */
class QueryBuilderMixinTest extends TestCase {
    /**
     * @covers ::getDefaultKeyName
     */
    public function testGetDefaultKeyNameQueryBuilder(): void {
        self::assertTrue(Builder::hasMacro('getDefaultKeyName'));
        self::assertEquals('id', $this->app->make('db')->query()->getDefaultKeyName());
    }

    /**
     * @covers ::getChunkedIterator
     */
    public function testIteratorQueryBuilder(): void {
        self::assertTrue(Builder::hasMacro('getChunkedIterator'));
        self::assertInstanceOf(Traversable::class, $this->app->make('db')->query()->getChunkedIterator());
    }

    /**
     * @covers ::getChangeSafeIterator
     */
    public function testChangeSafeIteratorQueryBuilder(): void {
        self::assertTrue(Builder::hasMacro('getChangeSafeIterator'));
        self::assertInstanceOf(Traversable::class, $this->app->make('db')->query()->getChangeSafeIterator());
    }
}
