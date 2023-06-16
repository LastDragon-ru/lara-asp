<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<InputValueDefinition>
 */
class InputFieldsDefinition extends ListBlock {
    /**
     * @param iterable<InputValueDefinitionNode>|iterable<InputObjectField> $fields
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable $fields,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($fields as $field) {
            $name        = $field instanceof InputValueDefinitionNode
                ? $field->name->value
                : $field->name;
            $this[$name] = new InputValueDefinition(
                $this->getContext(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $field,
            );
        }
    }

    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
