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
 * @covers \LastDragon_ru\LaraASP\Core\Concerns\InstanceCache
 */
class InstanceCacheTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testInstanceCacheGet(): void {
        $cache  = new InstanceCacheTest_Cache();
        $object = new stdClass();

        self::assertNull($cache->instanceCacheGet('a'));
        self::assertSame($object, $cache->instanceCacheGet('b', static function () use ($object): stdClass {
            return $object;
        }));
        self::assertSame($object, $cache->instanceCacheGet('b', static function (): stdClass {
            return new stdClass();
        }));
    }

    public function testInstanceCacheHas(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        self::assertFalse($cache->instanceCacheHas($key));
        self::assertNull($cache->instanceCacheGet($key, static function () {
            return null;
        }));
        self::assertTrue($cache->instanceCacheHas($key));
    }

    public function testInstanceCacheSet(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        self::assertFalse($cache->instanceCacheHas($key));
        self::assertNull($cache->instanceCacheSet($key, null));
        self::assertTrue($cache->instanceCacheHas($key));
    }

    public function testInstanceCacheUnset(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        self::assertTrue($cache->instanceCacheSet($key, true));
        self::assertTrue($cache->instanceCacheHas($key));
        self::assertTrue($cache->instanceCacheUnset($key));
        self::assertFalse($cache->instanceCacheHas($key));
    }

    public function testInstanceCacheClear(): void {
        $cache = new InstanceCacheTest_Cache();
        $key   = 'a';

        self::assertTrue($cache->instanceCacheSet($key, true));
        self::assertTrue($cache->instanceCacheHas($key));

        $cache->instanceCacheClear();

        self::assertFalse($cache->instanceCacheHas($key));
    }

    /**
     * @dataProvider dataProviderInstanceCacheKey
     */
    public function testInstanceCacheKey(string $expected, mixed $keys): void {
        self::assertEquals($expected, (new InstanceCacheTest_Cache())->instanceCacheKey($keys));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderInstanceCacheKey(): array {
        return [
            'string'                             => ['"string"', 'string'],
            'null'                               => ['null', null],
            'array'                              => ['[1,2,3]', [1, 2, 3]],
            'assoc'                              => [
                '{"a":"a","b":123,"c":true}',
                [
                    'b' => 123,
                    'c' => true,
                    'a' => 'a',
                ],
            ],
            'QueueableEntity without connection' => [
                sprintf(
                    '{"a":["%s",null,456],"b":123,"c":true}',
                    addslashes(strtolower(InstanceCacheTest_QueueableEntity::class)),
                ),
                [
                    'b' => 123,
                    'c' => true,
                    'a' => new InstanceCacheTest_QueueableEntity(456),
                ],
            ],
            'QueueableEntity with connection'    => [
                sprintf(
                    '{"a":["%s","connection",789],"b":123,"c":true}',
                    addslashes(strtolower(InstanceCacheTest_QueueableEntity::class)),
                ),
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

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

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
    private mixed $id;
    /**
     * @var array<string>
     */
    private array   $relations;
    private ?string $connection;

    public function __construct(mixed $id, string $connection = null) {
        $this->id         = $id;
        $this->relations  = ['ignored'];
        $this->connection = $connection;
    }

    public function getQueueableId(): mixed {
        return $this->id;
    }

    /**
     * @return array<string>
     */
    public function getQueueableRelations(): array {
        return $this->relations;
    }

    public function getQueueableConnection(): ?string {
        return $this->connection;
    }
}

// @phpcs:enable
