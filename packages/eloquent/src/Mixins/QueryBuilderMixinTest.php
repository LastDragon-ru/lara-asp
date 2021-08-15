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
        $this->assertTrue(Builder::hasMacro('getDefaultKeyName'));
        $this->assertEquals('id', $this->app->make('db')->query()->getDefaultKeyName());
    }

    /**
     * @covers ::getChunkedIterator
     */
    public function testIteratorQueryBuilder(): void {
        $this->assertTrue(Builder::hasMacro('getChunkedIterator'));
        $this->assertInstanceOf(Traversable::class, $this->app->make('db')->query()->getChunkedIterator());
    }

    /**
     * @covers ::getChangeSafeIterator
     */
    public function testChangeSafeIteratorQueryBuilder(): void {
        $this->assertTrue(Builder::hasMacro('getChangeSafeIterator'));
        $this->assertInstanceOf(Traversable::class, $this->app->make('db')->query()->getChangeSafeIterator());
    }
}
