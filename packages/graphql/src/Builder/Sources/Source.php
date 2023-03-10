<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

/**
 * @template TType of TypeDefinitionNode|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type
 */
class Source implements TypeSource {
    /**
     * @param TType $type
     */
    public function __construct(
        private Manipulator $manipulator,
        private TypeDefinitionNode|Node|Type $type,
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
    public function getType(): TypeDefinitionNode|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type {
        return $this->type;
    }

    public function getTypeName(): string {
        return $this->getManipulator()->getNodeTypeName($this->getType());
    }

    public function getTypeDefinition(): TypeDefinitionNode|Type {
        $type       = $this->getType();
        $definition = !($type instanceof TypeDefinitionNode)
            ? $this->getManipulator()->getTypeDefinitionNode($type)
            : $type;

        return $definition;
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
    // </editor-fold>
}
