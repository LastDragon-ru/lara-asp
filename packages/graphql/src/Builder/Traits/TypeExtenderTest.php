<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Exception;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidExtension;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeExtensionManipulator;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_string;

/**
 * @internal
 */
#[CoversClass(TypeExtender::class)]
final class TypeExtenderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderManipulateTypeExtension
     */
    public function testManipulateTypeExtension(Exception|string $expected, string $schema, string $type): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        Container::getInstance()->make(DirectiveLocator::class)
            ->setResolved('operator', TypeExtenderTest__Operator::class);

        $schema = $this->useGraphQLSchema($schema)->getGraphQLSchema();
        $type   = $schema->getType($type);

        self::assertNotNull($type);

        if (is_string($expected)) {
            $this->assertGraphQLPrintableEquals($expected, $type);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, string, string}>
     */
    public static function dataProviderManipulateTypeExtension(): array {
        $string = TypeExtenderTest__Scalar::class;
        $string = Cast::to(Node::class, AST::astFromValue($string, Type::string()));
        $string = Printer::doPrint($string);

        return [
            'Scalar'                      => [
                <<<GRAPHQL
                scalar MyScalar
                @scalar(
                    class: {$string}
                )
                @operator
                GRAPHQL,
                <<<GRAPHQL
                type Query {
                    test: String! @mock
                }

                scalar MyScalar
                @scalar(class: {$string})

                extend scalar MyScalar
                @operator
                GRAPHQL,
                'MyScalar',
            ],
            'Scalar (unknown)'            => [
                new TypeDefinitionUnknown('MyScalar'),
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                extend scalar MyScalar
                @operator
                GRAPHQL,
                'MyScalar',
            ],
            'Scalar (unknown extendable)' => [
                <<<GRAPHQL
                scalar MyScalarExtendable
                @scalar(
                    class: {$string}
                )
                @operator
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                extend scalar MyScalarExtendable
                @operator
                GRAPHQL,
                'MyScalarExtendable',
            ],
            'Enum'                        => [
                <<<'GRAPHQL'
                enum MyEnum
                @operator
                {
                    My
                }
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                enum MyEnum {
                    My
                }

                extend enum MyEnum
                @operator
                GRAPHQL,
                'MyEnum',
            ],
            'Type'                        => [
                <<<'GRAPHQL'
                type MyType
                @operator
                {
                    field: String
                }
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                type MyType {
                    field: String
                }

                extend type MyType
                @operator
                GRAPHQL,
                'MyType',
            ],
            'Input'                       => [
                <<<'GRAPHQL'
                input MyInput
                @operator
                {
                    field: String
                }
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                input MyInput {
                    field: String
                }

                extend input MyInput
                @operator
                GRAPHQL,
                'MyInput',
            ],
            'Interface'                   => [
                <<<'GRAPHQL'
                interface MyInterface
                @operator
                {
                    field: String
                }
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                interface MyInterface {
                    field: String
                }

                extend interface MyInterface
                @operator
                GRAPHQL,
                'MyInterface',
            ],
            'Invalid'                     => [
                new TypeDefinitionInvalidExtension('MyInterface', 'ObjectTypeExtension'),
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                interface MyInterface {
                    field: String
                }

                extend type MyInterface
                @operator
                GRAPHQL,
                'MyInterface',
            ],
            'Unsupported'                 => [
                <<<'GRAPHQL'
                union MyUnion =
                    | A
                    | B
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                type A {
                    a: String!
                }

                type B {
                    b: String!
                }

                union MyUnion = A | B

                extend union MyUnion
                @operator
                GRAPHQL,
                'MyUnion',
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
class TypeExtenderTest__Scalar extends ScalarType {
    #[Override]
    public function serialize(mixed $value): mixed {
        throw new Exception('Should not be called.');
    }

    #[Override]
    public function parseValue(mixed $value): mixed {
        throw new Exception('Should not be called.');
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parseLiteral(Node $valueNode, array $variables = null): mixed {
        throw new Exception('Should not be called.');
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TypeExtenderTest__Operator extends BaseDirective implements TypeExtensionManipulator {
    use TypeExtender;

    #[Override]
    public static function definition(): string {
        throw new Exception('Should not be called.');
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getExtendableScalars(): array {
        return [
            'MyScalarExtendable' => TypeExtenderTest__Scalar::class,
        ];
    }
}
