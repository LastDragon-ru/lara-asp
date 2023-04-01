<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use stdClass;

use function array_keys;
use function array_map;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator
 */
class AstManipulatorTest extends TestCase {
    public function testGetNodeInterfaces(): void {
        // Object
        $types       = $this->app->make(TypeRegistry::class);
        $builder     = new BuilderInfo(__METHOD__, stdClass::class);
        $document    = DocumentAST::fromSource(
        /** @lang GraphQL */
            <<<'GRAPHQL'
            interface InterfaceA {
                id: ID!
            }

            interface InterfaceB implements InterfaceA & InterfaceC {
                id: ID!
            }

            type ObjectA implements InterfaceA & InterfaceB {
                id: ID!
            }
            GRAPHQL,
        );
        $manipulator = $this->app->make(Manipulator::class, [
            'types'       => $types,
            'document'    => $document,
            'builderInfo' => $builder,
        ]);
        $interface   = new InterfaceType([
            'name'       => 'InterfaceC',
            'interfaces' => [
                static function (): InterfaceType {
                    return new InterfaceType([
                        'name'   => 'InterfaceD',
                        'fields' => [
                            'id' => [
                                'type' => Type::nonNull(Type::id()),
                            ],
                        ],
                    ]);
                },
            ],
            'fields'     => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                ],
            ],
        ]);

        $types->register($interface);
        $types->register(
            new ObjectType([
                'name'       => 'ObjectB',
                'interfaces' => [$interface],
                'fields'     => [
                    'id' => [
                        'type' => Type::nonNull(Type::id()),
                    ],
                ],
            ]),
        );

        // Object
        $object = $manipulator->getTypeDefinitionNode('ObjectA');

        self::assertInstanceOf(ObjectTypeDefinitionNode::class, $object);
        self::assertEquals(
            [
                'InterfaceA',
                'InterfaceB',
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getNodeInterfaces($object),
            ),
        );

        // ObjectType
        $objectType = $manipulator->getTypeDefinitionNode('ObjectB');

        self::assertInstanceOf(ObjectType::class, $objectType);
        self::assertEquals(
            [
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getNodeInterfaces($objectType),
            ),
        );

        // Interface
        $interface = $manipulator->getTypeDefinitionNode('InterfaceB');

        self::assertInstanceOf(InterfaceTypeDefinitionNode::class, $interface);
        self::assertEquals(
            [
                'InterfaceA',
                'InterfaceC',
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getNodeInterfaces($interface),
            ),
        );

        // InterfaceType
        $interfaceType = $manipulator->getTypeDefinitionNode('InterfaceC');

        self::assertInstanceOf(InterfaceType::class, $interfaceType);
        self::assertEquals(
            [
                'InterfaceD',
            ],
            array_keys(
                $manipulator->getNodeInterfaces($interfaceType),
            ),
        );
    }

    public function testGetNodeDirectives(): void {
        // Schema
        $this->useGraphQLSchema(
        /** @lang GraphQL */
            <<<'GRAPHQL'
            extend scalar Int @aDirective @bDirective
            scalar CustomScalar @bDirective @cDirective
            extend scalar CustomScalar @aDirective

            type Query {
                test: Test @all
            }

            type Test {
                id: ID!
            }
            GRAPHQL,
        );

        // Directives
        $locator = $this->app->make(DirectiveLocator::class);

        $locator->setResolved('aDirective', AstManipulatorTest_DirectiveA::class);
        $locator->setResolved('bDirective', AstManipulatorTest_DirectiveB::class);
        $locator->setResolved('cDirective', AstManipulatorTest_DirectiveC::class);

        // Prepare
        $map         = static function (Directive $directive): string {
            return $directive::class;
        };
        $builder     = new BuilderInfo(__METHOD__, stdClass::class);
        $manipulator = $this->app->make(Manipulator::class, [
            'document'    => $this->app->make(ASTBuilder::class)->documentAST(),
            'builderInfo' => $builder,
        ]);

        // Another class
        self::assertEquals(
            [
                // empty
            ],
            array_map(
                $map,
                $manipulator->getNodeDirectives(
                    $manipulator->getTypeDefinitionNode('CustomScalar'),
                    stdClass::class,
                ),
            ),
        );

        // Scalar node
        self::assertEquals(
            [
                AstManipulatorTest_DirectiveB::class,
                AstManipulatorTest_DirectiveC::class,
            ],
            array_map(
                $map,
                $manipulator->getNodeDirectives(
                    $manipulator->getTypeDefinitionNode('CustomScalar'),
                    Directive::class,
                ),
            ),
        );

        // Type
        self::assertEquals(
            [
                // Not supported by Lighthouse yet :(
            ],
            array_map(
                $map,
                $manipulator->getNodeDirectives(
                    Type::int(),
                    Directive::class,
                ),
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_DirectiveA implements Directive {
    public static function definition(): string {
        return 'directive @aDirective on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_DirectiveB implements Directive {
    public static function definition(): string {
        return 'directive @bDirective on OBJECT | SCALAR';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class AstManipulatorTest_DirectiveC implements Directive {
    public static function definition(): string {
        return 'directive @cDirective on OBJECT | SCALAR';
    }
}
