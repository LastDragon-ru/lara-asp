<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\Utils;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

use function is_string;
use function json_validate;
use function sprintf;

class JsonString extends StringType implements TypeDefinition {
    public string  $name        = 'JsonString';
    public ?string $description = 'Represents JSON string.';

    // <editor-fold desc="ScalarType">
    // =========================================================================
    public function serialize(mixed $value): string {
        return $this->validate($value, InvariantViolation::class);
    }

    public function parseValue(mixed $value): string {
        return $this->validate($value, Error::class);
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral(Node $valueNode, array $variables = null): string {
        if (!($valueNode instanceof StringValueNode)) {
            throw new Error(
                sprintf(
                    'The `%s` value expected, `%s` given.',
                    NodeKind::STRING,
                    $valueNode->kind,
                ),
            );
        }

        return $this->validate($valueNode->value, Error::class);
    }

    /**
     * @param class-string<Exception> $error
     *
     * @phpstan-assert string         $value
     */
    protected function validate(mixed $value, string $error): string {
        if (is_string($value) && json_validate($value)) {
            // ok
        } elseif ($value instanceof JsonStringable) {
            $value = (string) $value;
        } else {
            throw new $error(
                sprintf(
                    'The valid JSON string expected, `%s` given.',
                    Utils::printSafe($value),
                ),
            );
        }

        return $value;
    }
    // </editor-fold>

    // <editor-fold desc="TypeDefinition">
    // =========================================================================
    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        return $this->name();
    }

    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null {
        return $this;
    }
    // </editor-fold>
}
