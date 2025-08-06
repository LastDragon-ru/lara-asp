<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
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
#[CoversClass(Between::class)]
final class BetweenTest extends TestCase {
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
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'field'      => [
                    [
                        'query'    => 'select * from "test_objects" where "field" between ? and ?',
                        'bindings' => [1, 2],
                    ],
                    new Field('field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    null,
                ],
                'field.path' => [
                    [
                        'query'    => 'select * from "test_objects" where "path"."to"."field" between ? and ?',
                        'bindings' => [1, 2],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    null,
                ],
                'resolver'   => [
                    [
                        'query'    => 'select * from "test_objects" where "path__to__field" between ? and ?',
                        'bindings' => [1, 2],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
