<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

/**
 * @internal
 * @extends ObjectBlockList<EnumValue>
 */
class EnumValues extends ObjectBlockList {
    /**
     * @param Traversable<EnumValueDefinition>|array<EnumValueDefinition> $values
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $values,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($values as $value) {
            $this[$value->name] = new EnumValue(
                $this->getDispatcher(),
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
