<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<FieldDefinition>
 */
class FieldsDefinition extends ListBlock {
    /**
     * @param iterable<FieldDefinitionNode>|iterable<GraphQLFieldDefinition> $fields
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable $fields,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($fields as $field) {
            $name        = $field instanceof FieldDefinitionNode
                ? $field->name->value
                : $field->name;
            $this[$name] = new FieldDefinition(
                $this->getContext(),
                $level + 1,
                $used,
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
