<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Composer\InstalledVersions;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\ScoutBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
use function sprintf;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(NotIn::class)]
class NotInTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param Closure(static): Argument                               $argumentFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $operator = Container::getInstance()->make(NotIn::class);
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $context  = new Context();
        $search   = Mockery::mock(Handler::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument, $context);

        self::assertDatabaseQueryEquals($expected, $builder);
    }

    /**
     * @dataProvider dataProviderCallScout
     *
     * @param array<string, mixed>          $expected
     * @param Closure(static): ScoutBuilder $builderFactory
     * @param Closure(static): Argument     $argumentFactory
     * @param Closure():FieldResolver|null  $resolver
     */
    public function testCallScout(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
        Closure $resolver = null,
    ): void {
        // Prepare
        if ($resolver) {
            $this->override(FieldResolver::class, $resolver);
        }

        // Supported?
        $operator = Container::getInstance()->make(NotInTest_Operator::class);

        if (!$operator->isScoutSupported()) {
            self::markTestSkipped(sprintf(
                'Minimum version of `laravel/scout` should be `%s`, `%s` installed.',
                $operator->getScoutVersion(),
                InstalledVersions::getPrettyVersion('laravel/scout'),
            ));
        }

        // Test
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $context  = new Context();
        $search   = Mockery::mock(Handler::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument, $context);

        self::assertScoutQueryEquals($expected, $builder);
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
                        'query'    => 'select * from "test_objects" where "property" not in (?, ?, ?)',
                        'bindings' => [1, 2, 3],
                    ],
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                ],
                'property.path' => [
                    [
                        'query'    => 'select * from "test_objects" where "path"."to"."property" not in (?, ?, ?)',
                        'bindings' => ['a', 'b', 'c'],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[String!]!', ['a', 'b', 'c']);
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
                'property'               => [
                    [
                        'whereNotIns' => [
                            'path.to.property' => [1, 2, 3],
                        ],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                    null,
                ],
                'property with resolver' => [
                    [
                        'whereNotIns' => [
                            'properties/path/to/property' => [1, 2, 3],
                        ],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
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
