<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Closure;
use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AggregateDirective;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\CountDirective;
use Nuwave\Lighthouse\Schema\Directives\FindDirective;
use Nuwave\Lighthouse\Schema\Directives\FirstDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use PHPUnit\Framework\Attributes\CoversClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(WithBuilderInfo::class)]
class WithBuilderInfoTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGetNodeBuilderInfo
     *
     * @param array{name: string, builder: string} $expected
     * @param Closure(DirectiveLocator): Node      $nodeFactory
     */
    public function testGetNodeBuilderInfo(array $expected, Closure $nodeFactory): void {
        $directives = $this->app->make(DirectiveLocator::class);
        $node       = $nodeFactory($directives);
        $directive  = new class() extends BaseDirective {
            use WithBuilderInfo {
                getBuilderInfo as public;
            }

            public static function definition(): string {
                throw new Exception('should not be called.');
            }
        };

        $actual = $directive->getBuilderInfo($node);

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
     *     Closure(DirectiveLocator): FieldDefinitionNode,
     *     }>
     */
    public static function dataProviderGetNodeBuilderInfo(): array {
        return [
            'unknown'                                    => [
                [
                    'name'    => null,
                    'builder' => null,
                ],
                static function (): FieldDefinitionNode {
                    return Parser::fieldDefinition('field: String');
                },
            ],
            '@search'                                    => [
                [
                    'name'    => 'Scout',
                    'builder' => ScoutBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('search', SearchDirective::class);

                    return Parser::fieldDefinition('field(search: String @search): String');
                },
            ],
            '@all'                                       => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    return Parser::fieldDefinition('field: String @all');
                },
            ],
            '@all(query)'                                => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    $class = json_encode(WithBuilderInfoTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return $field;
                },
            ],
            '@all(custom query)'                         => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    $class = json_encode(WithBuilderInfoTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return $field;
                },
            ],
            '@paginate'                                  => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    return Parser::fieldDefinition('field: String @paginate');
                },
            ],
            '@paginate(resolver)'                        => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(WithBuilderInfoTest__PaginatorResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(resolver: {$class})");

                    return $field;
                },
            ],
            '@paginate(query)'                           => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(WithBuilderInfoTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

                    return $field;
                },
            ],
            '@paginate(custom query)'                    => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(WithBuilderInfoTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

                    return $field;
                },
            ],
            '@relation'                                  => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved(
                        'relation',
                        (new class () extends RelationDirective {
                            /** @noinspection PhpMissingParentConstructorInspection */
                            public function __construct() {
                                // empty
                            }

                            public static function definition(): string {
                                throw new Exception('should not be called.');
                            }
                        })::class,
                    );

                    $field = Parser::fieldDefinition('field: String @relation');

                    return $field;
                },
            ],
            '@find'                                      => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('find', FindDirective::class);

                    $field = Parser::fieldDefinition('field: String @find');

                    return $field;
                },
            ],
            '@first'                                     => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('first', FirstDirective::class);

                    $field = Parser::fieldDefinition('field: String @first');

                    return $field;
                },
            ],
            '@count'                                     => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('count', CountDirective::class);

                    $field = Parser::fieldDefinition('field: String @count');

                    return $field;
                },
            ],
            '@aggregate'                                 => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('aggregate', AggregateDirective::class);

                    $field = Parser::fieldDefinition('field: String @aggregate');

                    return $field;
                },
            ],
            '@aggregate(query)'                          => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('aggregate', AggregateDirective::class);

                    $class = json_encode(WithBuilderInfoTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @aggregate(builder: {$class})");

                    return $field;
                },
            ],
            BuilderInfoProvider::class                   => [
                [
                    'name'    => 'Custom',
                    'builder' => BuilderInfoProvider::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved(
                        'custom',
                        (new class () implements Directive, BuilderInfoProvider {
                            /** @noinspection PhpMissingParentConstructorInspection */
                            public function __construct() {
                                // empty
                            }

                            public static function definition(): string {
                                throw new Exception('should not be called.');
                            }

                            public function getBuilderInfo(): BuilderInfo|string|null {
                                return new BuilderInfo('Custom', BuilderInfoProvider::class);
                            }
                        })::class,
                    );

                    $field = Parser::fieldDefinition('field: String @custom');

                    return $field;
                },
            ],
            BuilderInfoProvider::class.' (class-string)' => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved(
                        'custom',
                        (new class () implements Directive, BuilderInfoProvider {
                            /** @noinspection PhpMissingParentConstructorInspection */
                            public function __construct() {
                                // empty
                            }

                            public static function definition(): string {
                                throw new Exception('should not be called.');
                            }

                            public function getBuilderInfo(): BuilderInfo|string|null {
                                return EloquentBuilder::class;
                            }
                        })::class,
                    );

                    $field = Parser::fieldDefinition('field: String @custom');

                    return $field;
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
class WithBuilderInfoTest__QueryBuilderResolver {
    public function __invoke(): QueryBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithBuilderInfoTest__CustomBuilderResolver {
    public function __invoke(): WithBuilderInfoTest__CustomBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithBuilderInfoTest__PaginatorResolver {
    public function __invoke(): mixed {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithBuilderInfoTest__CustomBuilder extends QueryBuilder {
    // empty
}
