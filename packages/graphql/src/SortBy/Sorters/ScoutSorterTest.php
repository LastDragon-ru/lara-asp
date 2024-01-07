<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
use function is_array;

/**
 * @internal
 */
#[CoversClass(ScoutSorter::class)]
class ScoutSorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSort
     *
     * @param array<string, mixed>|Exception $expected
     * @param Closure():FieldResolver|null   $resolver
     */
    public function testSort(
        array|Exception $expected,
        Property $property,
        Direction $direction,
        Closure $resolver = null,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(FieldResolver::class, $resolver);
        }

        $sorter  = Container::getInstance()->make(ScoutSorter::class);
        $builder = Container::getInstance()->make(ScoutBuilder::class, [
            'query' => '',
            'model' => new class() extends Model {
                // empty
            },
        ]);
        $builder = $sorter->sort($builder, $property, $direction, null);

        if (is_array($expected)) {
            self::assertScoutQueryEquals($expected, $builder);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderSort(): array {
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
                Direction::Desc,
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
                Direction::Asc,
                static function (): FieldResolver {
                    return new class() implements FieldResolver {
                        /**
                         * @inheritDoc
                         */
                        #[Override]
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
