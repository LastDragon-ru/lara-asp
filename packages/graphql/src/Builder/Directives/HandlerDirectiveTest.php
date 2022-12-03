<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Closure;
use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Mockery;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective
 */
class HandlerDirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getBuilderInfo
     *
     * @dataProvider dataProviderGetBuilderInfo
     *
     * @param array{name: string, builder: string}           $expected
     * @param Closure(DirectiveLocator): FieldDefinitionNode $fieldFactory
     */
    public function testGetBuilderInfo(array $expected, Closure $fieldFactory): void {
        $directives = $this->app->make(DirectiveLocator::class);
        $argFactory = Mockery::mock(ArgumentFactory::class);
        $container  = Mockery::mock(Container::class);
        $field      = $fieldFactory($directives);
        $directive  = new class($container, $argFactory, $directives) extends HandlerDirective {
            public static function definition(): string {
                throw new Exception('should not be called.');
            }

            public function getBuilderInfo(FieldDefinitionNode $field): BuilderInfo {
                return parent::getBuilderInfo($field);
            }

            protected function isTypeName(string $name): bool {
                return false;
            }

            protected function getArgDefinitionType(
                Manipulator $manipulator,
                DocumentAST $document,
                InputValueDefinitionNode $argument,
                FieldDefinitionNode $field,
            ): ListTypeNode|NamedTypeNode|NonNullTypeNode {
                throw new Exception('should not be called.');
            }
        };

        $actual = $directive->getBuilderInfo($field);

        self::assertEquals(
            $expected,
            [
                'name'    => $actual->getName(),
                'builder' => $actual->getBuilder()::class,
            ],
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *     array{name: string, builder: string},
     *     Closure(DirectiveLocator): FieldDefinitionNode,
     *     }>
     */
    public function dataProviderGetBuilderInfo(): array {
        return [
            'default'           => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    return Parser::fieldDefinition('field: String');
                },
            ],
            '@search'           => [
                [
                    'name'    => 'Scout',
                    'builder' => ScoutBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('search', SearchDirective::class);

                    return Parser::fieldDefinition('field(search: String @search): String');
                },
            ],
            '@all'              => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    return Parser::fieldDefinition('field: String @all');
                },
            ],
            '@all(query)'       => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    $class = json_encode(HandlerDirectiveTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return $field;
                },
            ],
            '@all(custom)'      => [
                [
                    'name'    => 'Query',
                    'builder' => HandlerDirectiveTest__CustomBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('all', AllDirective::class);

                    $class = json_encode(HandlerDirectiveTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @all(builder: {$class})");

                    return $field;
                },
            ],
            '@paginate'         => [
                [
                    'name'    => '',
                    'builder' => EloquentBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    return Parser::fieldDefinition('field: String @paginate');
                },
            ],
            '@paginate(query)'  => [
                [
                    'name'    => 'Query',
                    'builder' => QueryBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(HandlerDirectiveTest__QueryBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

                    return $field;
                },
            ],
            '@paginate(custom)' => [
                [
                    'name'    => 'Query',
                    'builder' => HandlerDirectiveTest__CustomBuilder::class,
                ],
                static function (DirectiveLocator $directives): FieldDefinitionNode {
                    $directives->setResolved('paginate', PaginateDirective::class);

                    $class = json_encode(HandlerDirectiveTest__CustomBuilderResolver::class, JSON_THROW_ON_ERROR);
                    $field = Parser::fieldDefinition("field: String @paginate(builder: {$class})");

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
class HandlerDirectiveTest__QueryBuilderResolver {
    public function __invoke(): QueryBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class HandlerDirectiveTest__CustomBuilderResolver {
    public function __invoke(): HandlerDirectiveTest__CustomBuilder {
        throw new Exception('should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class HandlerDirectiveTest__CustomBuilder extends QueryBuilder {
    // empty
}
