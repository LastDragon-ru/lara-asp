<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 * @extends TypeBlock<EnumValueDefinition>
 */
class EnumValue extends TypeBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        EnumValueDefinition $value,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $value);
    }

    protected function isBlock(): bool {
        return false;
    }

    protected function body(int $used): string {
        return $this->getName();
    }
}
