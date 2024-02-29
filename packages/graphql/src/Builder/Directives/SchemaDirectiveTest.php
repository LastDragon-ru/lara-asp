<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Exception;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionIsNotScalar;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionIsNotScalarExtension;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scalars\Internal;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_string;

/**
 * @internal
 */
#[CoversClass(SchemaDirective::class)]
final class SchemaDirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testInvoke(): void {
        $directive = new SchemaDirective__Directive();
        $actual    = $directive(new BuildSchemaString(''));
        $class     = self::getGraphQLStringValue(Internal::class);
        $expected  = "scalar SchemaDirective @scalar(class: {$class}) @schemaDirective__";

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderManipulateTypeDefinition
     */
    public function testManipulateTypeDefinition(Exception|string $expected, string $schema, string $type): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $directive                          = new SchemaDirective__Directive();
        $document                           = DocumentAST::fromSource($schema);
        $root                               = Parser::scalarTypeDefinition($directive(new BuildSchemaString($schema)));
        $document->types['SchemaDirective'] = $root;

        $directive->manipulateTypeDefinition($document, $root);

        $type = $document->types[$type] ?? null;

        self::assertNotNull($type);
        self::assertFalse(isset($document->types['SchemaDirective']));
        self::assertFalse(isset($document->typeExtensions['SchemaDirective']));

        if (is_string($expected)) {
            $this->assertGraphQLPrintableEquals($expected, $type);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    private static function getGraphQLStringValue(string $string): string {
        $string = Cast::to(Node::class, AST::astFromValue($string, Type::string()));
        $string = Printer::doPrint($string);

        return $string;
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, string, string}>
     */
    public static function dataProviderManipulateTypeDefinition(): array {
        $string  = self::getGraphQLStringValue(StringType::class);
        $default = self::getGraphQLStringValue(Internal::class);
        $custom  = self::getGraphQLStringValue(SchemaDirectiveTest__Scalar::class);

        return [
            'Scalar'                 => [
                <<<GRAPHQL
                scalar TestScalar
                @scalar(
                    class: {$default}
                )
                @test
                GRAPHQL,
                <<<GRAPHQL
                type Query {
                    test: String! @mock
                }

                scalar TestScalar
                @scalar(class: {$string})

                extend scalar TestScalar
                @test
                GRAPHQL,
                'TestScalar',
            ],
            'Scalar (no definition)' => [
                <<<GRAPHQL
                scalar TestScalar
                @scalar(
                    class: {$default}
                )
                @test
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                extend scalar TestScalar
                @test
                GRAPHQL,
                'TestScalar',
            ],
            'Scalar (custom class)'  => [
                <<<GRAPHQL
                scalar TestScalarCustom
                @scalar(
                    class: {$custom}
                )
                @test
                GRAPHQL,
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                extend scalar TestScalarCustom
                @test
                GRAPHQL,
                'TestScalarCustom',
            ],
            'Not a scalar'           => [
                new TypeDefinitionIsNotScalar('TestScalar', 'enum TestScalar'),
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                enum TestScalar {
                    TestCase
                }

                extend enum TestScalar
                @test
                GRAPHQL,
                'TestScalar',
            ],
            'Not a scalar extension' => [
                new TypeDefinitionIsNotScalarExtension('TestScalar', 'extend enum TestScalar'),
                <<<'GRAPHQL'
                type Query {
                    test: String! @mock
                }

                extend enum TestScalar
                @test
                GRAPHQL,
                'TestScalar',
            ],
            'Unsupported'            => [
                <<<'GRAPHQL'
                union TestUnion =
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

                union TestUnion = A | B

                extend union TestUnion
                @test
                GRAPHQL,
                'TestUnion',
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
class SchemaDirective__Directive extends SchemaDirective {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getScalars(AstManipulator $manipulator): array {
        return [
            'TestScalar'       => null,
            'TestScalarCustom' => SchemaDirectiveTest__Scalar::class,
        ];
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SchemaDirectiveTest__Scalar extends ScalarType {
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
