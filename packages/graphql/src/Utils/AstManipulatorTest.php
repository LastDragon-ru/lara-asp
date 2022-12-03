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
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use stdClass;

use function array_keys;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator
 */
class AstManipulatorTest extends TestCase {
    /**
     * @covers ::getNodeInterfaces
     */
    public function testGetNodeInterfaces(): void {
        // Object
        $types       = $this->app->make(TypeRegistry::class);
        $builder     = new BuilderInfo(__METHOD__, new stdClass());
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

        $types->register(
            new ObjectType([
                'name'       => 'ObjectB',
                'interfaces' => [
                    static function () use ($types): Type {
                        return $types->get('InterfaceC');
                    },
                ],
                'fields'     => [
                    [
                        'name' => 'id',
                        'type' => Type::nonNull(Type::id()),
                    ],
                ],
            ]),
        );
        $types->register(
            new InterfaceType([
                'name'       => 'InterfaceC',
                'interfaces' => [
                    static function (): InterfaceType {
                        return new InterfaceType([
                            'name'   => 'InterfaceD',
                            'fields' => [
                                [
                                    'name' => 'id',
                                    'type' => Type::nonNull(Type::id()),
                                ],
                            ],
                        ]);
                    },
                ],
                'fields'     => [
                    [
                        'name' => 'id',
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
}
