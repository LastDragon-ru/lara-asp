<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

use function is_string;

/**
 * @template TType of TypeDefinitionNode|(Node&TypeNode)|Type
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
    public function getType(): TypeDefinitionNode|Node|Type {
        return $this->type;
    }

    public function getTypeName(): string {
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

    public function create(TypeDefinitionNode|Node|Type|string $type): TypeSource {
        return is_string($type)
            ? new Source($this->getManipulator(), Parser::typeReference($type))
            : new Source($this->getManipulator(), $type);
    }
    // </editor-fold>
}