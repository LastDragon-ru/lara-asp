<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * @extends TypeSource<InputObjectTypeDefinitionNode|InputObjectType, TypeSource<TypeDefinitionNode|TypeNode|Type>|null>
 */
class InputNodeSource extends TypeSource {
    public function getField(InputValueDefinitionNode|FieldDefinition $field): InputFieldSource {
        return new InputFieldSource($this->getManipulator(), $this->getType(), $field);
    }
}
