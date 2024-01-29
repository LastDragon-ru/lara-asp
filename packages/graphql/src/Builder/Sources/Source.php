<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Override;

/**
 * @template TType of (TypeDefinitionNode&Node)|(TypeNode&Node)|Type
 * @template TParent of TypeSource|null
 */
class Source implements TypeSource {
    /**
     * @param TType   $type
     * @param TParent $parent
     */
    public function __construct(
        private AstManipulator $manipulator,
        private TypeDefinitionNode|TypeNode|Type $type,
        private TypeSource|null $parent = null,
    ) {
        // empty
    }

    // <editor-fold desc="Getter / Setter">
    // =========================================================================
    protected function getManipulator(): AstManipulator {
        return $this->manipulator;
    }

    /**
     * @return TParent
     */
    public function getParent(): ?TypeSource {
        return $this->parent;
    }
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @return TType
     */
    #[Override]
    public function getType(): TypeDefinitionNode|TypeNode|Type {
        return $this->type;
    }

    #[Override]
    public function getTypeName(): string {
        return $this->getManipulator()->getTypeName($this->getType());
    }

    /**
     * @return (TypeDefinitionNode&Node)|Type
     */
    #[Override]
    public function getTypeDefinition(): TypeDefinitionNode|Type {
        return $this->getManipulator()->getTypeDefinition($this->getType());
    }

    #[Override]
    public function isNullable(): bool {
        return $this->getManipulator()->isNullable($this->getType());
    }

    #[Override]
    public function isList(): bool {
        return $this->getManipulator()->isList($this->getType());
    }

    #[Override]
    public function isUnion(): bool {
        return $this->getManipulator()->isUnion($this->getType());
    }

    #[Override]
    public function isObject(): bool {
        return $this->getManipulator()->isObject($this->getType());
    }

    #[Override]
    public function __toString(): string {
        return $this->getManipulator()->getTypeFullName($this->getType());
    }
    // </editor-fold>
}
