<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(NullsFirst::class)]
final class NullsFirstTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param Closure(static): Argument                               $argumentFactory
     * @param Closure(static): Context|null                           $contextFactory
     * @param Closure(object, Field): string|null                     $resolver
     */
    #[DataProvider('dataProviderCall')]
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        $this->testDatabaseOperator(
            Directive::class,
            $expected,
            $builderFactory,
            $field,
            $argumentFactory,
            $contextFactory,
            $resolver,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCall(): array {
        $argument = static function (self $test): Argument {
            $test->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test(input: Test @sortBy): String! @all
                }

                input Test {
                    a: String
                }
                GRAPHQL,
            );

            return $test->getGraphQLArgument(
                'SortByRootTest!',
                [
                    'nullsFirst' => [
                        'a' => Direction::Desc,
                    ],
                ],
            );
        };

        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'field'    => [
                    [
                        'query'    => 'select * from "test_objects" order by "a" DESC NULLS FIRST',
                        'bindings' => [],
                    ],
                    new Field('operator name should be ignored'),
                    $argument,
                    null,
                    null,
                ],
                'resolver' => [
                    [
                        'query'    => 'select * from "test_objects" order by "resolved__a" DESC NULLS FIRST',
                        'bindings' => [],
                    ],
                    new Field('operator name should be ignored'),
                    $argument,
                    null,
                    static function (object $builder, Field $field): string {
                        return 'resolved__'.implode('__', $field->getPath());
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
