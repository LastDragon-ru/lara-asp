<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;
use stdClass;

use function is_string;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\ModelHelper
 */
class ModelHelperTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getRelation
     *
     * @dataProvider dataProviderGetRelation
     *
     * @param Exception|class-string $expected
     */
    public function testGetRelation(Exception|string $expected, Closure $model, string $name): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = (new ModelHelper($model()))->getRelation($name);

        if (is_string($expected)) {
            $this->assertInstanceOf($expected, $actual);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderGetRelation(): array {
        return (new CompositeDataProvider(
            new ArrayDataProvider([
                'model'   => [
                    new UnknownValue(),
                    static function (): Model {
                        return new ModelHelperTest__Model();
                    },
                ],
                'builder' => [
                    new UnknownValue(),
                    static function (): Builder {
                        return (new ModelHelperTest__Model())->query();
                    },
                ],
            ]),
            new ArrayDataProvider([
                'noTypeHint'  => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'noTypeHint'),
                    'noTypeHint',
                ],
                'notRelation' => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'notRelation'),
                    'notRelation',
                ],
                'union'       => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'union'),
                    'union',
                ],
                'ok'          => [
                    BelongsTo::class,
                    'ok',
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ModelHelperTest__Model extends Model {
    /**
     * @noinspection             PhpMissingReturnTypeInspection
     * @phpcsSuppress            SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     * @phpstan-ignore-next-line Required for test
     */
    public function noTypeHint() {
        return $this->belongsTo(self::class);
    }

    public function notRelation(): stdClass {
        return new stdClass();
    }

    public function union(): BelongsTo|HasOne {
        return $this->belongsTo(self::class);
    }

    public function ok(): BelongsTo {
        return $this->belongsTo(self::class);
    }
}
