<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToCast;
use LastDragon_ru\LaraASP\Serializer\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AsSerializable::class)]
class AsSerializableTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGet
     */
    public function testGet(?object $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $cast   = new AsSerializable(AsSerializableTest__Serializable::class);
        $actual = $cast->get(Mockery::mock(Model::class), 'key', $value, []);

        self::assertEquals($expected, $actual);
    }

    public function testSet(): void {
        $cast  = new AsSerializable(AsSerializableTest__Serializable::class);
        $model = Mockery::mock(Model::class);

        self::assertEquals(['key' => null], $cast->set($model, 'key', null, []));
        self::assertEquals(
            ['key' => '{"property":"value"}'],
            $cast->set($model, 'key', new AsSerializableTest__Serializable('value'), []),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{object|null, mixed}>
     */
    public static function dataProviderGet(): array {
        return [
            'null'                => [
                null,
                null,
            ],
            'string'              => [
                new AsSerializableTest__Serializable('value'),
                '{"property":"value"}',
            ],
            'unexpected'          => [
                new FailedToCast(
                    AsSerializableTest__Serializable::class,
                    123,
                ),
                12_345,
            ],
            'expected instance'   => [
                new AsSerializableTest__Serializable('value'),
                new AsSerializableTest__Serializable('value'),
            ],
            'unexpected instance' => [
                new FailedToCast(
                    AsSerializableTest__Serializable::class,
                    new AsSerializableTest__AnotherSerializable('another'),
                ),
                new AsSerializableTest__AnotherSerializable('another'),
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
class AsSerializableTest__Serializable implements Serializable {
    public function __construct(
        public string $property,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AsSerializableTest__AnotherSerializable implements Serializable {
    public function __construct(
        public string $property,
    ) {
        // empty
    }
}
