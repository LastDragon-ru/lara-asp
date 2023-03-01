<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use Stringable;

use function is_string;

/**
 * @template TType of TypeDefinitionNode|TypeNode|Type
 * @template TSource of TypeSource<TypeDefinitionNode|TypeNode|Type>|null
 */
class TypeSource implements Stringable {
    /**
     * @param TType   $type
     * @param TSource $source
     */
    public function __construct(
        private Manipulator $manipulator,
        private TypeDefinitionNode|TypeNode|Type $type,
        private ?TypeSource $source = null,
    ) {
        // empty
    }

    // <editor-fold desc="Getter / Setter">
    // =========================================================================
    protected function getManipulator(): Manipulator {
        return $this->manipulator;
    }
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @return TType
     */
    public function getType(): TypeDefinitionNode|TypeNode|Type {
        return $this->type;
    }

    public function getName(): string {
        return $this->getManipulator()->getNodeTypeName($this->getType());
    }

    public function isNullable(): ?bool {
        return $this->getManipulator()->isNullable($this->getType());
    }

    public function isList(): ?bool {
        return $this->getManipulator()->isList($this->getType());
    }

    public function __toString(): string {
        return $this->getManipulator()->getNodeTypeFullName($this->getType());
    }

    /**
     * @return TSource
     */
    public function getSource(): ?TypeSource {
        return $this->source;
    }

    /**
     * @template T of TypeDefinitionNode|TypeNode|Type
     *
     * @param T|string $type
     *
     * @return ($type is string ? TypeSource<TypeNode, TType> : TypeSource<T, TType>)
     */
    public function getDerivative(TypeDefinitionNode|TypeNode|Type|string $type): TypeSource {
        if (is_string($type)) {
            $type = Parser::typeReference($type);
        }

        return new TypeSource($this->getManipulator(), $type, $this);
    }
    // </editor-fold>
}
