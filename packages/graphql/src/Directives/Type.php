<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Directives;

use GraphQL\Type\Definition\PhpEnumType;
use GraphQL\Type\Definition\Type as GraphQLType;
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

    #[Override]
    public static function definition(): string {
        $name     = DirectiveLocator::directiveName(static::class);
        $argClass = self::ArgClass;

        return <<<GRAPHQL
            """
            Converts scalar into GraphQL Type.
            """
            directive @{$name}(
                """
                Reference to a PHP Class/Enum (FQN).
                """
                {$argClass}: String!
            ) on SCALAR
        GRAPHQL;
    }

    #[Override]
    public function resolveNode(TypeValue $value): GraphQLType {
        // Enum?
        $class = Cast::toString($this->directiveArgValue(self::ArgClass));

        if (!is_a($class, UnitEnum::class, true)) {
            throw new DefinitionException("The `{$class}` is not an enum.");
        }

        // Return
        return new PhpEnumType($class, $value->getTypeDefinitionName());
    }
}
