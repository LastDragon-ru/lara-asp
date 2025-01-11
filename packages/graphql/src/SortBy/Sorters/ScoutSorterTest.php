<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Requirements\RequiresLaravelScout;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;
use function is_array;

/**
 * @internal
 */
#[CoversClass(ScoutSorter::class)]
#[RequiresLaravelScout]
final class ScoutSorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string, mixed>|Exception      $expected
     * @param Closure(object, Field): string|null $resolver
     */
    #[DataProvider('dataProviderSort')]
    public function testSort(
        array|Exception $expected,
        Field $field,
        Direction $direction,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver !== null) {
            $this->override(
                BuilderFieldResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getField')
                        ->atLeast()
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $sorter  = $this->app()->make(ScoutSorter::class);
        $builder = $this->app()->make(ScoutBuilder::class, [
            'query' => '',
            'model' => new class() extends Model {
                // empty
            },
        ]);
        $builder = $sorter->sort($builder, $field, $direction, null);

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
            'clause'   => [
                [
                    'orders' => [
                        [
                            'column'    => 'c.d.e',
                            'direction' => 'desc',
                        ],
                    ],
                ],
                new Field('c', 'd', 'e'),
                Direction::Desc,
                null,
                null,
            ],
            'resolver' => [
                [
                    'orders' => [
                        [
                            'column'    => 'a__b',
                            'direction' => 'asc',
                        ],
                    ],
                ],
                new Field('a', 'b'),
                Direction::Asc,
                static function (object $builder, Field $field): string {
                    return implode('__', $field->getPath());
                },
                null,
            ],
        ];
    }
    // </editor-fold>
}
