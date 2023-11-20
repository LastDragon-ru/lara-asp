<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Closure;
use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Mockery;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AggregateDirective;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\CountDirective;
use Nuwave\Lighthouse\Schema\Directives\FindDirective;
use Nuwave\Lighthouse\Schema\Directives\FirstDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @internal
 */
#[CoversClass(BuilderInfoDetector::class)]
class BuilderInfoDetectorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGetNodeBuilderInfo
     *
     * @param array{name: string, builder: string}                                                                                                       $expected
     * @param Closure(DirectiveLocator, AstManipulator): (InterfaceFieldArgumentSource|ObjectFieldArgumentSource|ObjectFieldSource|InterfaceFieldSource) $sourceFactory
     */
    public function testGetNodeBuilderInfo(array $expected, Closure $sourceFactory): void {
        $manipulator = $this->app->make(AstManipulator::class, ['document' => Mockery::mock(DocumentAST::class)]);
        $locator     = $this->app->make(DirectiveLocator::class);
        $source      = $sourceFactory($locator, $manipulator);
        $directive   = new class() extends BuilderInfoDetector {
            #[Override]
            public function getBuilderInfo(
                AstManipulator $manipulator,
                InterfaceFieldArgumentSource|ObjectFieldArgumentSource|ObjectFieldSource|InterfaceFieldSource $source,
            ): ?BuilderInfo {
                return parent::getBuilderInfo($manipulator, $source);
            }
        };

        $actual = $directive->getBuilderInfo($manipulator, $source);

        self::assertEquals(
            $expected,
            [
                'name'    => $actual?->getName(),
                'builder' => $actual?->getBuilder(),
            ],
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *     array{name: string, builder: string}|array{name: null, builder: null},
     *     Closure(DirectiveLocator, AstManipulator): (InterfaceFieldArgumentSource|ObjectFieldArgumentSource|ObjectFieldSource|InterfaceFieldSource),
     *     }>
     */
    public static function dataProviderGetNodeBuilderInfo(): array {
        return [
            'unknown'                  => [
                [
                    'name'    => null,
                    'builder' => null,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String'),
                    );
                },
            ],
            '@search'                  => [
                [
                    'name'    => 'Scout',
                    'builder' => ScoutBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('search', SearchDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field(search: String @search): String'),
                    );
                },
            ],
            '@all'                     => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('all', AllDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @all'),
                    );
                },
            ],
            '@all(query)'              => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('all', AllDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            '@all(custom query)'       => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('all', AllDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            '@paginate'                => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('paginate', PaginateDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @paginate'),
                    );
                },
            ],
            '@paginate(resolver)'      => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__PaginatorResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(resolver: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            '@paginate(query)'         => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            '@paginate(custom query)'  => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            '@relation'                => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved(
                        'relation',
                        (new class () extends RelationDirective {
                            /** @noinspection PhpMissingParentConstructorInspection */
                            public function __construct() {
                                // empty
                            }

                            #[Override]
                            public static function definition(): string {
                                throw new Exception('should not be called.');
                            }
                        })::class,
                    );

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @relation'),
                    );
                },
            ],
            '@find'                    => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('find', FindDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @find'),
                    );
                },
            ],
            '@first'                   => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('first', FirstDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @first'),
                    );
                },
            ],
            '@count'                   => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('count', CountDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @count'),
                    );
                },
            ],
            '@aggregate'               => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('aggregate', AggregateDirective::class);

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @aggregate'),
                    );
                },
            ],
            '@aggregate(query)'        => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved('aggregate', AggregateDirective::class);

                    $class = json_encode(BuilderInfoDetectorTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @aggregate(builder: {$class})");

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        $field,
                    );
                },
            ],
            BuilderInfoProvider::class => [
                [
                    'name'    => 'Custom',
                    'builder' => BuilderInfoProvider::class,
                ],
                static function (DirectiveLocator $locator, AstManipulator $manipulator): ObjectFieldSource {
                    $locator->setResolved(
                        'custom',
                        (new class () implements Directive, BuilderInfoProvider {
                            /** @noinspection PhpMissingParentConstructorInspection */
                            public function __construct() {
                                // empty
                            }

                            #[Override]
                            public static function definition(): string {
                                throw new Exception('should not be called.');
                            }

                            #[Override]
                            public function getBuilderInfo(TypeSource $source): ?BuilderInfo {
                                return new BuilderInfo('Custom', BuilderInfoProvider::class);
                            }
                        })::class,
                    );

                    return new ObjectFieldSource(
                        $manipulator,
                        new ObjectType(['name' => 'Test', 'fields' => []]),
                        Parser::fieldDefinition('field: String @custom'),
                    );
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
class BuilderInfoDetectorTest__QueryBuilderResolver {
    public function __invoke(): QueryBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BuilderInfoDetectorTest__CustomBuilderResolver {
    public function __invoke(): BuilderInfoDetectorTest__CustomBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BuilderInfoDetectorTest__PaginatorResolver {
    public function __invoke(): mixed {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BuilderInfoDetectorTest__CustomBuilder extends QueryBuilder {
    // empty
}
