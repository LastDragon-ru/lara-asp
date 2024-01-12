<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(Random::class)]
final class RandomTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param Closure(static): Argument                               $argumentFactory
     * @param Closure(static): Context|null                           $contextFactory
     * @param Closure(object, Property): string|null                  $resolver
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        $this->testOperator(
            Directive::class,
            $expected,
            $builderFactory,
            $property,
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
                'property'      => [
                    [
                        'query'    => 'select * from "test_objects" order by RANDOM()',
                        'bindings' => [],
                    ],
                    new Property('property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('SortByTypeFlag', 'yes');
                    },
                    null,
                    null,
                ],
                'property.path' => [
                    [
                        'query'    => 'select * from "test_objects" order by RANDOM()',
                        'bindings' => [],
                    ],
                    new Property('path', 'to', 'property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('SortByTypeFlag', 'yes');
                    },
                    null,
                    null,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
