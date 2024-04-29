<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Directives;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\PhpEnumType;
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

class Type extends BaseDirective implements TypeResolver {
    final protected const ArgClass = 'class';

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $argClass  = self::ArgClass;
        $nodeClass = TypeDefinitionNode::class;
        $typeClass = GraphQLType::class.'&'.NamedType::class;

        return <<<GRAPHQL
            """
            Converts scalar into GraphQL Type.
            """
            directive @{$name}(
                """
                Reference to a PHP Class/Enum (FQN).

                If not PHP Enum, the Laravel Container with the following additional
                arguments will be used to resolver the instance.

                * `string \$name` - the type name.
                * `{$nodeClass} \$node` - the AST node.

                Resolved instance must be an `{$typeClass}` and have a name equal
                to `\$name` argument.
                """
                {$argClass}: String!
            ) on SCALAR
        GRAPHQL;
    }

    /**
     * @return GraphQLType&NamedType
     */
    #[Override]
    public function resolveNode(TypeValue $value): GraphQLType {
        // Type?
        $class = Cast::toString($this->directiveArgValue(self::ArgClass));
        $name  = $value->getTypeDefinitionName();
        $type  = match (true) {
            is_a($class, GraphQLType::class, true) => $this->container->getInstance()->make($class, [
                // @phpcs:disable Squiz.Arrays.ArrayDeclaration.DoubleArrowNotAligned
                // https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/475
                'name' => $name,
                'node' => $value->getTypeDefinition(),
                // @phpcs:enable
            ]),
            is_a($class, UnitEnum::class, true)    => new PhpEnumType($class, $name),
            default                                => null,
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
}
