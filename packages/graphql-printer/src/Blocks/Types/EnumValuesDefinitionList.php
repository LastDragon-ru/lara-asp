<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;
use Traversable;

/**
 * @internal
 * @extends ObjectBlockList<EnumValueDefinitionBlock>
 */
class EnumValuesDefinitionList extends ObjectBlockList {
    /**
     * @param Traversable<EnumValueDefinition>|array<EnumValueDefinition> $values
     */
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        Traversable|array $values,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($values as $value) {
            $this[$value->name] = new EnumValueDefinitionBlock(
                $this->getSettings(),
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
