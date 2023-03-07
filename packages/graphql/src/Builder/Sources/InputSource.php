<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;

/**
 * @extends Source<InputObjectTypeDefinitionNode|InputObjectType>
 */
class InputSource extends Source {
    public function getField(InputValueDefinitionNode|FieldDefinition $field): InputFieldSource {
        return new InputFieldSource($this->getManipulator(), $this->getType(), $field);
    }
}
