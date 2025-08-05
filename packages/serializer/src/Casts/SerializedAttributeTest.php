<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToCast;
use LastDragon_ru\LaraASP\Serializer\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(SerializedAttribute::class)]
final class SerializedAttributeTest extends TestCase {
    public function testGet(): void {
        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);
        $expected   = new SerializedAttributeTest__Serializable('value');
        $actual     = ($attribute->get)('{"property":"value"}', []);

        self::assertEquals($expected, $actual);
    }

    public function testGetNull(): void {
        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);
        $actual     = ($attribute->get)(null, []);

        self::assertNull($actual);
    }

    public function testGetTypeNotMatch(): void {
        self::expectException(FailedToCast::class);
        self::expectExceptionMessage(
            sprintf(
                'Failed to cast `%s` to `%s`.',
                'integer',
                SerializedAttributeTest__Serializable::class,
            ),
        );

        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);

        ($attribute->get)(123, []);
    }

    public function testSet(): void {
        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);
        $expected   = '{"property":"value"}';
        $actual     = ($attribute->set)(new SerializedAttributeTest__Serializable('value'), []);

        self::assertEquals($expected, $actual);
    }

    public function testSetNull(): void {
        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);
        $actual     = ($attribute->set)(null, []);

        self::assertNull($actual);
    }

    public function testSetTypeNotMatch(): void {
        self::expectException(FailedToCast::class);
        self::expectExceptionMessage(
            sprintf(
                'Failed to cast `%s` to `%s`.',
                stdClass::class,
                SerializedAttributeTest__Serializable::class,
            ),
        );

        $serializer = $this->app()->make(Serializer::class);
        $attribute  = new SerializedAttribute($serializer, SerializedAttributeTest__Serializable::class);

        ($attribute->set)(new stdClass(), []);
    }

    public function testModelAttribute(): void {
        SerializedAttributeTest__Model::$serializer = $this->app()->make(Serializer::class);
        $attributes                                 = [
            'value' => '{"property":"default"}',
        ];
        $model                                      = (new SerializedAttributeTest__Model())->newFromBuilder(
            $attributes,
        );

        self::assertEquals(
            new SerializedAttributeTest__Serializable('default'),
            $model->value,
        );

        $value        = new SerializedAttributeTest__Serializable('value');
        $model->value = $value;

        self::assertEquals($value, $model->value);
        self::assertSame('{"property":"value"}', $model->getAttributes()['value'] ?? null);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SerializedAttributeTest__Serializable implements Serializable {
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
class SerializedAttributeTest__Model extends Model {
    public static Serializer $serializer;

    /**
     * @return Attribute<?SerializedAttributeTest__Serializable, ?SerializedAttributeTest__Serializable>
     */
    protected function value(): Attribute {
        return (new Serialized(self::$serializer))->attribute(SerializedAttributeTest__Serializable::class);
    }
}
