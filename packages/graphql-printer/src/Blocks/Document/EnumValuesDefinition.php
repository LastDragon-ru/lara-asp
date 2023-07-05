<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ObjectBlockList<EnumValueDefinition>
 */
class EnumValuesDefinition extends ObjectBlockList {
    /**
     * @param iterable<EnumValueDefinitionNode>|iterable<GraphQLEnumValueDefinition> $values
     */
    public function __construct(
        Context $context,
        iterable $values,
    ) {
        parent::__construct($context);

        foreach ($values as $value) {
            $name        = $value instanceof EnumValueDefinitionNode
                ? $value->name->value
                : $value->name;
            $this[$name] = new EnumValueDefinition(
                $this->getContext(),
                $value,
            );
        }
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeEnums();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
