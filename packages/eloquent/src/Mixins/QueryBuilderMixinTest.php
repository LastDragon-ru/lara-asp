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
     * @covers ::iterator
     */
    public function testIteratorQueryBuilder(): void {
        $this->assertTrue(Builder::hasMacro('iterator'));
        $this->assertInstanceOf(Traversable::class, $this->app->make('db')->query()->iterator());
    }
}
