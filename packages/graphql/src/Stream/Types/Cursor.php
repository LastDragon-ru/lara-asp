<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\Utils;
use Illuminate\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor as StreamCursor;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

use function filter_var;
use function is_int;
use function is_string;
use function sprintf;

use const FILTER_VALIDATE_INT;

class Cursor extends ScalarType implements TypeDefinition {
    public string  $name        = Directive::Name.'Cursor';
    public ?string $description = <<<'DESCRIPTION'
        Represents a cursor for the `@stream` directive. The value can be a
        positive `Int` or a `String`. The `Int` value represents the offset
        (zero-based) to navigate to any position within the stream (= offset
        pagination). And the `String` value represents the cursor and allows
        navigation only to the previous/current/next pages (= cursor
        pagination).
        DESCRIPTION;

    // <editor-fold desc="Getters/Setters">
    // =========================================================================
    protected function getEncrypter(): Encrypter {
        return Container::getInstance()->make(Encrypter::class);
    }

    protected function getSerializer(): Serializer {
        return Container::getInstance()->make(Serializer::class);
    }
    // </editor-fold>

    // <editor-fold desc="ScalarType">
    // =========================================================================
    /**
     * @return string|int<0, max>
     */
    public function serialize(mixed $value): string|int {
        $value = $this->validate($value, InvariantViolation::class);

        if ($value instanceof StreamCursor) {
            $value = $this->getSerializer()->serialize($value, 'json');
            $value = $this->encrypt($value);
        }

        return $value;
    }

    public function parseValue(mixed $value): StreamCursor|int {
        if (is_string($value)) {
            try {
                $value = $this->decrypt($value);
                $value = $this->getSerializer()->deserialize(StreamCursor::class, $value, 'json');
            } catch (Exception $exception) {
                throw new Error('The Cursor is not valid.', previous: $exception);
            }
        } else {
            $value = $this->validate($value, Error::class);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral(Node $valueNode, array $variables = null): StreamCursor|int {
        $value = null;

        if ($valueNode instanceof StringValueNode) {
            $value = $this->parseValue($valueNode->value);
        } elseif ($valueNode instanceof IntValueNode) {
            $value = filter_var($valueNode->value, FILTER_VALIDATE_INT);
            $value = $this->parseValue($value);
        } else {
            throw new Error(
                sprintf(
                    'The `%s`/`%s` value expected, `%s` given.',
                    NodeKind::STRING,
                    NodeKind::INT,
                    $valueNode->kind,
                ),
            );
        }

        return $value;
    }

    /**
     * @param class-string<Exception>           $error
     *
     * @phpstan-assert StreamCursor|int<0, max> $value
     */
    protected function validate(mixed $value, string $error): StreamCursor|int {
        if ($value instanceof StreamCursor) {
            // ok
        } elseif (is_int($value)) {
            if ($value < 0) {
                throw new $error('The offset must be greater or equal to 0.');
            }
        } else {
            throw new $error(
                sprintf(
                    'The valid Cursor expected, `%s` given.',
                    Utils::printSafe($value),
                ),
            );
        }

        return $value;
    }

    protected function encrypt(string $value): string {
        return $this->getEncrypter()->encrypt($value, false);
    }

    protected function decrypt(string $value): string {
        return Cast::toString($this->getEncrypter()->decrypt($value, false));
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