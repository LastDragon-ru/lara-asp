<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Contains
 *
 * @phpstan-import-type BuilderFactory from \LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider
 */
class ContainsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     * @covers ::escape
     *
     * @dataProvider dataProviderApply
     * @param array{query: string, bindings: array<mixed>} $expected
     * @param BuilderFactory                               $builder
     * @param class-string<Grammar>                        $grammar
     */
    public function testApply(
        array $expected,
        Closure $builder,
        string $grammar,
        string $property,
        mixed $value,
    ): void {
        $builder = $builder($this);
        $grammar = new $grammar();

        if ($builder instanceof EloquentBuilder) {
            $builder->toBase()->grammar = $grammar;
        } else {
            $builder->grammar = $grammar;
        }

        $operator = $this->app->make(Contains::class);
        $builder  = $operator->apply($builder, $property, $value);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                MySqlGrammar::class     => [
                    [
                        'query'    => 'select * from `tmp` where `property` LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    MySqlGrammar::class,
                    'property',
                    '%a[_]c!%',
                ],
                SQLiteGrammar::class    => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    'property',
                    '%a[_]c!%',
                ],
                PostgresGrammar::class  => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    PostgresGrammar::class,
                    'property',
                    '%a[_]c!%',
                ],
                SqlServerGrammar::class => [
                    [
                        'query'    => 'select * from [tmp] where [property] LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a![!_!]c!!!%%'],
                    ],
                    SqlServerGrammar::class,
                    'property',
                    '%a[_]c!%',
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
