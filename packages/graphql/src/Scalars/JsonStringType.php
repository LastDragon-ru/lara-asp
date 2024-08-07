<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\StringType;
use GraphQL\Utils\Utils;
use Override;

use function is_string;
use function json_validate;
use function sprintf;

class JsonStringType extends StringType {
    public string  $name        = 'JsonString';
    public ?string $description = 'Represents JSON string.';

    // <editor-fold desc="ScalarType">
    // =========================================================================
    #[Override]
    public function serialize(mixed $value): string {
        if ($value instanceof JsonStringable) {
            $value = (string) $value;
        } else {
            $value = $this->validate($value, InvariantViolation::class);
        }

        return $value;
    }

    #[Override]
    public function parseValue(mixed $value): string {
        return $this->validate($value, Error::class);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parseLiteral(Node $valueNode, ?array $variables = null): string {
        if (!($valueNode instanceof StringValueNode)) {
            throw new Error(
                sprintf(
                    'The `%s` value expected, `%s` given.',
                    NodeKind::STRING,
                    $valueNode->kind,
                ),
            );
        }

        return $this->parseValue($valueNode->value);
    }

    /**
     * @param class-string<Exception> $error
     *
     * @phpstan-assert string         $value
     */
    protected function validate(mixed $value, string $error): string {
        if (is_string($value) && json_validate($value)) {
            // ok
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
}
