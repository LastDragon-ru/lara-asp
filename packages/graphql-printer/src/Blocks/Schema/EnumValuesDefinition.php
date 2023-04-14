<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Traversable;

/**
 * @internal
 * @extends ObjectBlockList<EnumValueDefinition>
 */
class EnumValuesDefinition extends ObjectBlockList {
    /**
     * @param Traversable<GraphQLEnumValueDefinition>|array<GraphQLEnumValueDefinition> $values
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        Traversable|array $values,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($values as $value) {
            $this[$value->name] = new EnumValueDefinition(
                $this->getContext(),
                $this->getLevel() + 1,
                $this->getUsed(),
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
