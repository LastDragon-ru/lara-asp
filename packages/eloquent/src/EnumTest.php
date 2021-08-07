<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Enum
 */
class EnumTest extends TestCase {
    use WithTestObject;

    /**
     * @covers ::castUsing
     */
    public function testCastUsing(): void {
        // From string/int
        $model        = new EnumTest_Model();
        $model->value = 'a';
        $model->save();

        $this->assertEquals($model->refresh()->value, EnumTest_Enum::a());

        // From enum
        $model->value = EnumTest_Enum::a();
        $model->save();

        $this->assertEquals($model->refresh()->value, EnumTest_Enum::a());

        // Null
        $model->value = null;
        $model->save();

        $this->assertNull($model->refresh()->value);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumTest_Enum extends Enum {
    public static function a(): static {
        return self::make(__FUNCTION__);
    }

    public static function i(): static {
        return self::make(1);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @property string $value
 */
class EnumTest_Model extends TestObject {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var array<string>
     */
    protected $casts = [
        'value' => EnumTest_Enum::class,
    ];
}
