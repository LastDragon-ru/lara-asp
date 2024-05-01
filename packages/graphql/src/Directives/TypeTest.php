<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Directives;

use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Type\Definition\PhpEnumType;
use GraphQL\Type\Definition\StringType;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\PhpEnumTypeHelper;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Type::class)]
final class TypeTest extends TestCase {
    public function testResolveNodeEnum(): void {
        $class = TypeTest_Enum::class;
        $attr  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestEnum';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @type(class: {$attr})
            GRAPHQL,
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $type     = $registry->get($name);

        self::assertInstanceOf(PhpEnumType::class, $type);
        self::assertEquals($name, $type->name());
        self::assertEquals($class, PhpEnumTypeHelper::getEnumClass($type));
    }

    public function testResolveNodeType(): void {
        $class = TypeTest_Type::class;
        $attr  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestType';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @type(class: {$attr})
            GRAPHQL,
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $type     = $registry->get($name);

        self::assertInstanceOf($class, $type);
        self::assertEquals($name, $type->name());
        self::assertEquals($class, $type::class);
    }

    public function testResolveNodeScalar(): void {
        $class = TypeTest_Scalar::class;
        $attr  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestScalar';
        $desc  = 'Description.';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            """
            {$desc}
            """
            scalar {$name} @type(class: {$attr})
            GRAPHQL,
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $type     = $registry->get($name);

        self::assertInstanceOf($class, $type);
        self::assertEquals($name, $type->name());
        self::assertEquals($desc, $type->description());
        self::assertEquals($class, $type::class);
    }

    public function testResolveNodeInvalidName(): void {
        $class = TypeTest_ScalarInvalidName::class;
        $attr  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestScalar';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @type(class: {$attr})
            GRAPHQL,
        );

        self::expectException(DefinitionException::class);
        self::expectExceptionMessage(
            "The type name must be `{$name}`, `{$name}-changed` given (`scalar {$name}`).",
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $registry->get($name);
    }

    public function testResolveNodeNotGraphQLType(): void {
        $class = stdClass::class;
        $attr  = json_encode($class, JSON_THROW_ON_ERROR);
        $name  = 'TestType';

        $this->useGraphQLSchema(
            <<<GRAPHQL
            scalar {$name} @type(class: {$attr})
            GRAPHQL,
        );

        self::expectException(DefinitionException::class);
        self::expectExceptionMessage(
            "The `{$class}` is not a GraphQL type (`scalar {$name}`).",
        );

        $registry = Container::getInstance()->make(TypeRegistry::class);
        $registry->get($name);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum TypeTest_Enum {
    case A;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TypeTest_Type extends StringType {
    public function __construct(string $name, ScalarTypeDefinitionNode $node) {
        parent::__construct([
            'name'    => $name,
            'astNode' => $node,
        ]);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TypeTest_Scalar extends StringType {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TypeTest_ScalarInvalidName extends StringType {
    public function __construct(string $name, ScalarTypeDefinitionNode $node) {
        parent::__construct([
            'name'    => "{$name}-changed",
            'astNode' => $node,
        ]);
    }
}
