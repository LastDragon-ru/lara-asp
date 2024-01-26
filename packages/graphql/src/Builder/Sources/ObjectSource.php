<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * @extends Source<ObjectTypeDefinitionNode|ObjectType, null>
 */
class ObjectSource extends Source {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function getField(
        FieldDefinitionNode|FieldDefinition $field,
        TypeNode|Type $type = null,
    ): ObjectFieldSource {
        return new ObjectFieldSource($this->getManipulator(), $this, $field, $type);
    }
}
