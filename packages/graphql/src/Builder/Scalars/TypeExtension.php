<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\TypeExtender as TypeExtenderTrait;
use Override;

/**
 * @internal
 * @see TypeExtenderTrait
 */
class TypeExtension extends ScalarType {
    public ?string $description = <<<'STRING'
        Special/Internal scalar that used to extend types and exists only as a
        definition. Must not be used as type/value.
        STRING;

    #[Override]
    public function serialize(mixed $value): mixed {
        $this->error(InvariantViolation::class);
    }

    #[Override]
    public function parseValue(mixed $value): mixed {
        $this->error(Error::class);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parseLiteral(Node $valueNode, array $variables = null): mixed {
        $this->error(Error::class);
    }

    /**
     * @param class-string<Exception> $error
     */
    protected function error(string $error): never {
        throw new $error('The scalar is internal and cannot be used as a type/value.');
    }
}
