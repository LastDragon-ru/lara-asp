<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;

/**
 * @internal
 * @extends DefinitionBlock<EnumValueDefinition>
 */
class EnumValueDefinitionBlock extends DefinitionBlock {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        EnumValueDefinition $value,
    ) {
        parent::__construct($settings, $level, $used, $value);
    }

    protected function type(): string|null {
        return null;
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
