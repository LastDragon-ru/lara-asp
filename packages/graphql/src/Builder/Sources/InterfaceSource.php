<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;

/**
 * @extends Source<InterfaceTypeDefinitionNode|InterfaceType, null>
 */
class InterfaceSource extends Source {
    public function getField(FieldDefinitionNode|FieldDefinition $field): InterfaceFieldSource {
        return new InterfaceFieldSource($this->getManipulator(), $this, $field);
    }
}
