<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Closure;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

use function is_string;

/**
 * @internal
 */
#[CoversClass(ModelHelper::class)]
final class ModelHelperTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Exception|class-string                                $expected
     * @param Closure(): (Builder<Model>|Model|class-string<Model>) $model
     */
    #[DataProvider('dataProviderGetRelation')]
    public function testGetRelation(Exception|string $expected, Closure $model, string $name): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $actual = (new ModelHelper($model()))->getRelation($name);

        if (is_string($expected)) {
            self::assertInstanceOf($expected, $actual);
        }
    }

    /**
     * @param Exception|class-string                                $expected
     * @param Closure(): (Builder<Model>|Model|class-string<Model>) $model
     */
    #[DataProvider('dataProviderGetRelation')]
    public function testIsRelation(Exception|string $expected, Closure $model, string $name): void {
        $actual   = (new ModelHelper($model()))->isRelation($name);
        $expected = !($expected instanceof Exception);

        self::assertEquals($expected, $actual);
    }

    /**
     * @param Closure(): (Builder<Model>|Model|class-string<Model>) $model
     */
    #[DataProvider('dataProviderIsSoftDeletable')]
    public function testIsSoftDeletable(bool $expected, Closure $model): void {
        $actual = (new ModelHelper($model()))->isSoftDeletable();

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderGetRelation(): array {
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
                        return ModelHelperTest__Model::query();
                    },
                ],
            ]),
            new ArrayDataProvider([
                'noTypeHint'       => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'noTypeHint'),
                    'noTypeHint',
                ],
                'notRelation'      => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'notRelation'),
                    'notRelation',
                ],
                'union'            => [
                    BelongsTo::class,
                    'union',
                ],
                'unionNotRelation' => [
                    new PropertyIsNotRelation(new ModelHelperTest__Model(), 'unionNotRelation'),
                    'unionNotRelation',
                ],
                'intersection'     => [
                    BelongsTo::class,
                    'intersection',
                ],
                'ok'               => [
                    BelongsTo::class,
                    'ok',
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<string, array{bool, Closure(): mixed}>
     */
    public static function dataProviderIsSoftDeletable(): array {
        return [
            'model'                 => [
                false,
                static function (): Model {
                    return new ModelHelperTest__Model();
                },
            ],
            'model (SoftDeletes)'   => [
                true,
                static function (): Model {
                    return new ModelHelperTest__ModelSoftDeletes();
                },
            ],
            'builder'               => [
                false,
                static function (): Builder {
                    return ModelHelperTest__Model::query();
                },
            ],
            'builder (SoftDeletes)' => [
                true,
                static function (): Builder {
                    return ModelHelperTest__ModelSoftDeletes::query();
                },
            ],
            'class'                 => [
                false,
                static function (): string {
                    return ModelHelperTest__Model::class;
                },
            ],
            'class (SoftDeletes)'   => [
                true,
                static function (): string {
                    return ModelHelperTest__ModelSoftDeletes::class;
                },
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
class ModelHelperTest__Model extends Model {
    /**
     * @noinspection             PhpMissingReturnTypeInspection
     * @phpcsSuppress            SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     * @phpstan-ignore-next-line missingType.return (Required for test)
     */
    public function noTypeHint() {
        return $this->belongsTo(self::class);
    }

    public function notRelation(): stdClass {
        return new stdClass();
    }

    /**
     * @return BelongsTo<self,self>|HasOne<self>
     */
    public function union(): BelongsTo|HasOne {
        return $this->belongsTo(self::class);
    }

    /**
     * @return BelongsTo<self,self>|stdClass
     */
    public function unionNotRelation(): BelongsTo|stdClass {
        return $this->belongsTo(self::class);
    }

    /**
     * @return BelongsTo<self,self>&BuilderContract
     */
    public function intersection(): BelongsTo&BuilderContract {
        return $this->belongsTo(self::class);
    }

    /**
     * @return BelongsTo<self,self>
     */
    public function ok(): BelongsTo {
        return $this->belongsTo(self::class);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ModelHelperTest__ModelSoftDeletes extends Model {
    use SoftDeletes;
}
