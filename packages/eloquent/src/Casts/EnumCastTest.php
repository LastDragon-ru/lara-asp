<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Casts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Eloquent\Enum;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Casts\EnumCast
 */
class EnumCastTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::get
     *
     * @dataProvider dataProviderGet
     */
    public function testGet(Exception|Enum|null $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $cast  = new EnumCast(EnumCastTest_Enum::class);
        $key   = '';
        $attrs = [];
        $model = new class() extends Model {
            // empty
        };

        $this->assertEquals($expected, $cast->get($model, $key, $value, $attrs));
    }

    /**
     * @covers ::set
     *
     * @dataProvider dataProviderSet
     */
    public function testSet(Exception|string|int|null $expected, mixed $value) {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $cast  = new EnumCast(EnumCastTest_Enum::class);
        $key   = '';
        $attrs = [];
        $model = new class() extends Model {
            // empty
        };

        $this->assertEquals($expected, $cast->set($model, $key, $value, $attrs));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderGet(): array {
        return [
            'int'    => [
                EnumCastTest_Enum::i(),
                1,
            ],
            'string' => [
                EnumCastTest_Enum::a(),
                'a',
            ],
            'self'   => [
                EnumCastTest_Enum::i(),
                EnumCastTest_Enum::i(),
            ],
            'null'   => [
                null,
                null,
            ],
            'bool'   => [
                new InvalidArgumentException(sprintf(
                    'Type `%s` cannot be converted into `%s` enum.',
                    gettype(true),
                    EnumCastTest_Enum::class,
                )),
                true,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderSet(): array {
        return [
            'self (int)'    => [
                1,
                EnumCastTest_Enum::i(),
            ],
            'self (string)' => [
                'a',
                EnumCastTest_Enum::a(),
            ],
            'null'          => [
                null,
                null,
            ],
            'bool'          => [
                new InvalidArgumentException(sprintf(
                    'Type `%s` cannot be converted into `%s` enum.',
                    gettype(true),
                    EnumCastTest_Enum::class,
                )),
                true,
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
class EnumCastTest_Enum extends Enum {
    public static function a(): static {
        return self::make(__FUNCTION__);
    }

    public static function i(): static {
        return self::make(1);
    }
}
