<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Directives;

use GraphQL\Type\Definition\PhpEnumType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\TypeValue;
use Nuwave\Lighthouse\Support\Contracts\TypeResolver;
use Override;
use UnitEnum;

use function is_a;

/**
 * @internal
 */
class AsEnum extends BaseDirective implements TypeResolver {
    final protected const ArgClass = 'class';

    #[Override]
    public static function definition(): string {
        $name                = DirectiveLocator::directiveName(static::class);
        $argClass            = self::ArgClass;
        $classPhpEnumType    = PhpEnumType::class;
        $classAstManipulator = AstManipulator::class;

        return <<<GRAPHQL
            """
            Internal directive that used by `{$classAstManipulator}` to register `{$classPhpEnumType}`.
            """
            directive @{$name}(
                """
                Reference to a PHP Enum class.
                """
                {$argClass}: String!
            ) on SCALAR
        GRAPHQL;
    }

    #[Override]
    public function resolveNode(TypeValue $value): Type {
        // Enum?
        $class = Cast::toString($this->directiveArgValue(self::ArgClass));

        if (!is_a($class, UnitEnum::class, true)) {
            throw new DefinitionException("The `{$class}` is not an enum.");
        }

        // Return
        return new PhpEnumType($class, $value->getTypeDefinitionName());
    }
}
