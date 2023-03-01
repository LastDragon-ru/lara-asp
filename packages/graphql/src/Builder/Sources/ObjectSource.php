<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * @extends TypeSource<ObjectTypeDefinitionNode|ObjectType, TypeSource<TypeDefinitionNode|TypeNode|Type>|null>
 */
class ObjectSource extends TypeSource {
    public function getField(FieldDefinitionNode|FieldDefinition $field): ObjectFieldSource {
        return new ObjectFieldSource($this->getManipulator(), $this->getType(), $field);
    }
}
