<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Illuminate\Contracts\Encryption\StringEncrypter;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset as StreamOffset;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

use function filter_var;
use function is_int;
use function is_string;
use function sprintf;

use const FILTER_VALIDATE_INT;

/**
 * @phpstan-import-type ScalarConfig from ScalarType
 */
class Offset extends ScalarType {
    public string  $name        = Directive::Name.'Offset';
    public ?string $description = <<<'DESCRIPTION'
        Represents a offset for the `@stream` directive. The value can be a
        positive `Int` or a `String`. The `Int` value represents the offset
        (zero-based) to navigate to any position within the stream (= offset
        pagination). And the `String` value represents the cursor and allows
        navigation only to the previous/current/next pages (= cursor
        pagination).
        DESCRIPTION;

    /**
     * @param ScalarConfig $config
     */
    public function __construct(
        protected readonly StringEncrypter $encrypter,
        protected readonly Serializer $serializer,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    // <editor-fold desc="ScalarType">
    // =========================================================================
    /**
     * @return string|int<0, max>
     */
    #[Override]
    public function serialize(mixed $value): string|int {
        $value = $this->validate($value, InvariantViolation::class);

        if ($value instanceof StreamOffset) {
            $value = $this->serializer->serialize($value, 'json');
            $value = $this->encrypter->encryptString($value);
        }

        return $value;
    }

    /**
     * @return StreamOffset|int<0, max>
     */
    #[Override]
    public function parseValue(mixed $value): StreamOffset|int {
        if (is_string($value)) {
            try {
                $value = Cast::toString($this->encrypter->decryptString($value));
                $value = $this->serializer->deserialize(StreamOffset::class, $value, 'json');
            } catch (Exception) {
                throw new Error('The cursor is not valid.');
            }
        } else {
            $value = $this->validate($value, Error::class);
        }

        return $value;
    }

    /**
     * @inheritDoc
     * @return StreamOffset|int<0, max>
     */
    #[Override]
    public function parseLiteral(Node $valueNode, array $variables = null): StreamOffset|int {
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
     * @phpstan-assert StreamOffset|int<0, max> $value
     */
    protected function validate(mixed $value, string $error): StreamOffset|int {
        if ($value instanceof StreamOffset) {
            // ok
        } elseif (is_int($value)) {
            if ($value < 0) {
                throw new $error('The offset must be greater or equal to 0.');
            }
        } else {
            throw new $error(
                sprintf(
                    'The valid cursor/offset expected, `%s` given.',
                    Utils::printSafe($value),
                ),
            );
        }

        return $value;
    }
}
