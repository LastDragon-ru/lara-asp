<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Directives;

use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\PhpEnumType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type as GraphQLType;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\TypeValue;
use Nuwave\Lighthouse\Support\Contracts\TypeResolver;
use Override;
use UnitEnum;

use function is_a;

/**
 * @phpstan-import-type ScalarConfig from ScalarType
 */
class Type extends BaseDirective implements TypeResolver {
    final protected const ArgClass = 'class';

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name        = DirectiveLocator::directiveName(static::class);
        $argClass    = self::ArgClass;
        $nodeClass   = ScalarTypeDefinitionNode::class;
        $typeClass   = GraphQLType::class.'&'.NamedType::class;
        $scalarClass = ScalarType::class;

        return <<<GRAPHQL
            """
            Converts scalar into GraphQL Type. Similar to Lighthouse's `@scalar`
            directive, but uses Laravel Container to resolve instance and also
            supports PHP enums.
            """
            directive @{$name}(
                """
                Reference to a PHP Class/Enum (FQN).

                If not PHP Enum, the Laravel Container with the following additional
                arguments will be used to resolver the instance:

                * `string \$name` - the type name.
                * `{$nodeClass} \$node` - the AST node.
                * `array&ScalarConfig \$config` - the scalar configuration (if `{$scalarClass}`).

                Resolved instance must be an `{$typeClass}` and have a name equal
                to `\$name` argument.
                """
                {$argClass}: String!
            ) on SCALAR
        GRAPHQL;
    }

    #[Override]
    public function resolveNode(TypeValue $value): GraphQLType&NamedType {
        // Type?
        $class = Cast::toString($this->directiveArgValue(self::ArgClass));
        $node  = Cast::to(ScalarTypeDefinitionNode::class, $value->getTypeDefinition());
        $name  = $value->getTypeDefinitionName();
        $type  = match (true) {
            is_a($class, GraphQLType::class, true) && is_a($class, NamedType::class, true)
                => $this->createType($name, $class, $node),
            is_a($class, UnitEnum::class, true)
                => $this->createEnum($name, $class, $node),
            default
                => null,
        };

        if (!($type instanceof GraphQLType) || !($type instanceof NamedType)) {
            throw new DefinitionException(
                "The `{$class}` is not a GraphQL type (`scalar {$name}`).",
            );
        } elseif ($type->name() !== $name) {
            throw new DefinitionException(
                "The type name must be `{$name}`, `{$type->name()}` given (`scalar {$name}`).",
            );
        } else {
            // ok
        }

        // Return
        return $type;
    }

    /**
     * @param class-string<UnitEnum> $class
     */
    private function createEnum(string $name, string $class, ScalarTypeDefinitionNode $node): EnumType {
        return new PhpEnumType($class, $name);
    }

    /**
     * @param class-string<GraphQLType&NamedType> $class
     */
    private function createType(string $name, string $class, ScalarTypeDefinitionNode $node): object {
        $args = [
            'name' => $name,
            'node' => $node,
        ];

        if (is_a($class, ScalarType::class, true)) {
            $args['config'] = $this->createTypeScalarConfig($name, $class, $node);
        }

        return $this->container->getInstance()->make($class, $args);
    }

    /**
     * @return ScalarConfig
     */
    private function createTypeScalarConfig(string $name, string $class, ScalarTypeDefinitionNode $node): array {
        return [
            'name'        => $name,
            'astNode'     => $node,
            'description' => $node->description?->value,
        ];
    }
}
