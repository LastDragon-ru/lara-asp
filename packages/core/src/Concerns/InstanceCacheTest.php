<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Contracts\Queue\QueueableEntity;
use PHPUnit\Framework\TestCase;
use stdClass;
use function addslashes;
use function sprintf;
use function strtolower;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Concerns\InstanceCache
 */
class InstanceCacheTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::instanceCacheGet
     */
    public function testInstanceCacheGet(): void {
        $cache  = new InstanceCacheTest_Cache();
        $object = new stdClass();

        $this->assertNull($cache->instanceCacheGet('a'));
        $this->assertSame($object, $cache->instanceCacheGet('b', function () use ($object) {
            return $object;
        }));
        $this->assertSame($object, $cache->instanceCacheGet('b', function () {
            return new stdClass();
        }));
    }

    /**
     * @covers ::instanceCacheHas
     */
    public function testInstanceCacheHas(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        $this->assertFalse($cache->instanceCacheHas($key));
        $this->assertNull($cache->instanceCacheGet($key, function () {
            return null;
        }));
        $this->assertTrue($cache->instanceCacheHas($key));
    }

    /**
     * @covers ::instanceCacheSet
     */
    public function testInstanceCacheSet(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        $this->assertFalse($cache->instanceCacheHas($key));
        $this->assertNull($cache->instanceCacheSet($key, null));
        $this->assertTrue($cache->instanceCacheHas($key));
    }

    /**
     * @covers ::instanceCacheUnset
     */
    public function testInstanceCacheUnset(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        $this->assertTrue($cache->instanceCacheSet($key, true));
        $this->assertTrue($cache->instanceCacheHas($key));
        $this->assertTrue($cache->instanceCacheUnset($key));
        $this->assertFalse($cache->instanceCacheHas($key));
    }

    public function testInstanceCacheClear(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        $this->assertTrue($cache->instanceCacheSet($key, true));
        $this->assertTrue($cache->instanceCacheHas($key));

        $cache->instanceCacheClear();

        $this->assertFalse($cache->instanceCacheHas($key));
    }

    /**
     * @covers ::instanceCacheKey
     *
     * @dataProvider dataProviderInstanceCacheKey
     *
     * @param string $expected
     * @param mixed  $keys
     *
     * @return void
     */
    public function testInstanceCacheKey(string $expected, $keys): void {
        $this->assertEquals($expected, (new InstanceCacheTest_Cache())->instanceCacheKey($keys));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderInstanceCacheKey(): array {
        return [
            'string'                             => ['"string"', 'string'],
            'null'                               => ['null', null],
            'array'                              => ['[1,2,3]', [1, 2, 3]],
            'assoc'                              => ['{"a":"a","b":123,"c":true}', [
                'b' => 123,
                'c' => true,
                'a' => 'a',
            ]],
            'QueueableEntity without connection' => [
                sprintf('{"a":["%s",null,456],"b":123,"c":true}', addslashes(strtolower(InstanceCacheTest_QueueableEntity::class))),
                [
                    'b' => 123,
                    'c' => true,
                    'a' => new InstanceCacheTest_QueueableEntity(456),
                ],
            ],
            'QueueableEntity with connection'    => [
                sprintf('{"a":["%s","connection",789],"b":123,"c":true}', addslashes(strtolower(InstanceCacheTest_QueueableEntity::class))),
                [
                    'b' => 123,
                    'c' => true,
                    'a' => new InstanceCacheTest_QueueableEntity(789, 'connection'),
                ],
            ],
        ];
    }
    // </editor-fold>
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class InstanceCacheTest_Cache {
    use InstanceCache {
        instanceCacheKey as public;
        instanceCacheGet as public;
        instanceCacheHas as public;
        instanceCacheSet as public;
        instanceCacheUnset as public;
        instanceCacheClear as public;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class InstanceCacheTest_QueueableEntity implements QueueableEntity {
    private         $id;
    private array   $relations;
    private ?string $connection;

    public function __construct($id, string $connection = null) {
        $this->id         = $id;
        $this->relations  = ['ignored'];
        $this->connection = $connection;
    }

    public function getQueueableId() {
        return $this->id;
    }

    public function getQueueableRelations() {
        return $this->relations;
    }

    public function getQueueableConnection() {
        return $this->connection;
    }
}
