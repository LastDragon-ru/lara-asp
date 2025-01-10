<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\ScoutBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Requirements\RequiresLaravelScout;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;
use function sprintf;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(NotIn::class)]
final class NotInTest extends TestCase {
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

    /**
     * @param array<string, mixed>                 $expected
     * @param Closure(static): ScoutBuilder<Model> $builderFactory
     * @param Closure(static): Argument            $argumentFactory
     * @param Closure(static): Context|null        $contextFactory
     * @param Closure(object, Field): string|null  $resolver
     * @param Closure():FieldResolver|null         $fieldResolver
     */
    #[DataProvider('dataProviderCallScout')]
    #[RequiresLaravelScout]
    public function testCallScoutBuilder(
        array $expected,
        Closure $builderFactory,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
        ?Closure $fieldResolver,
    ): void {
        // Prepare
        if ($fieldResolver !== null) {
            $this->override(FieldResolver::class, $fieldResolver);
        }

        // Supported?
        $operator = $this->app()->make(NotInTest_Operator::class);

        if (!$operator->isScoutSupported()) {
            self::markTestSkipped(
                sprintf(
                    'Minimum version of `laravel/scout` should be `%s`, `%s` installed.',
                    $operator->getScoutVersion(),
                    InstalledVersions::getPrettyVersion('laravel/scout'),
                ),
            );
        }

        // Test
        $this->testScoutOperator(
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
                        'query'    => 'select * from "test_objects" where "field" not in (?, ?, ?)',
                        'bindings' => [1, 2, 3],
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
                        'query'    => 'select * from "test_objects" where "path"."to"."field" not in (?, ?, ?)',
                        'bindings' => ['a', 'b', 'c'],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[String!]!', ['a', 'b', 'c']);
                    },
                    null,
                    null,
                ],
                'resolver'   => [
                    [
                        'query'    => 'select * from "test_objects" where "path__to__field" not in (?, ?, ?)',
                        'bindings' => ['a', 'b', 'c'],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[String!]!', ['a', 'b', 'c']);
                    },
                    null,
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCallScout(): array {
        return (new CompositeDataProvider(
            new ScoutBuilderDataProvider(),
            new ArrayDataProvider([
                'field'                 => [
                    [
                        'whereNotIns' => [
                            'path.to.field' => [1, 2, 3],
                        ],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    null,
                    null,
                ],
                'resolver (deprecated)' => [
                    [
                        'whereNotIns' => [
                            'properties/path/to/property' => [1, 2, 3],
                        ],
                    ],
                    new Property('path', 'to', 'property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    null,
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
                'resolver'              => [
                    [
                        'whereNotIns' => [
                            'path__to__field' => [1, 2, 3],
                        ],
                    ],
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                    null,
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
class NotInTest_Operator extends NotIn {
    #[Override]
    public function isScoutSupported(): bool {
        return parent::isScoutSupported();
    }

    #[Override]
    public function getScoutVersion(): ?string {
        return parent::getScoutVersion();
    }
}
