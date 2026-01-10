<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * @extends Source<InputObjectTypeDefinitionNode|InputObjectType, null>
 */
class InputSource extends Source {
    public function getField(
        InputValueDefinitionNode|InputObjectField $field,
        (TypeNode&Node)|Type|null $type = null,
    ): InputFieldSource {
        return new InputFieldSource($this->getManipulator(), $this, $field, $type);
    }
}
