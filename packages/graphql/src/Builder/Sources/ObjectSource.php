<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;

/**
 * @extends Source<ObjectTypeDefinitionNode|ObjectType>
 */
class ObjectSource extends Source {
    public function getField(FieldDefinitionNode|FieldDefinition $field): ObjectFieldSource {
        return new ObjectFieldSource($this->getManipulator(), $this->getType(), $field);
    }
}
