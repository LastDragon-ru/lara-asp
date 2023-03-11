<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function implode;
use function is_array;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder
 */
class BuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderHandle
     *
     * @param array<string, mixed>|Exception $expected
     * @param Closure():FieldResolver|null   $resolver
     */
    public function testHandle(
        array|Exception $expected,
        Property $property,
        string $direction,
        Closure $resolver = null,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(FieldResolver::class, $resolver);
        }

        $builder = $this->app->make(ScoutBuilder::class, [
            'query' => '',
            'model' => new class() extends Model {
                // empty
            },
        ]);
        $builder = $this->app->make(Builder::class)->handle($builder, $property, $direction);

        if (is_array($expected)) {
            self::assertScoutQueryEquals($expected, $builder);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderHandle(): array {
        return [
            'clause'               => [
                [
                    'orders' => [
                        [
                            'column'    => 'c.d.e',
                            'direction' => 'desc',
                        ],
                    ],
                ],
                new Property('c', 'd', 'e'),
                'desc',
            ],
            'clause with resolver' => [
                [
                    'orders' => [
                        [
                            'column'    => 'properties/a/b',
                            'direction' => 'asc',
                        ],
                    ],
                ],
                new Property('a', 'b'),
                'asc',
                static function (): FieldResolver {
                    return new class() implements FieldResolver {
                        /**
                         * @inheritDoc
                         */
                        public function getField(Model $model, Property $property): string {
                            return 'properties/'.implode('/', $property->getPath());
                        }
                    };
                },
            ],
        ];
    }
    // </editor-fold>
}
