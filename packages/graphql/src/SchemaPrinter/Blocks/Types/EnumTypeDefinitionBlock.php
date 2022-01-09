<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<EnumType>
 */
class EnumTypeDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        EnumType $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $definition);
    }

    protected function type(): string {
        return 'enum';
    }

    protected function body(int $used): Block|string|null {
        $space  = $this->space();
        $values = new EnumValuesDefinitionList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $used + mb_strlen($space),
            $this->getDefinition()->getValues(),
        );

        return "{$space}{$values}";
    }

    protected function fields(): Block|string|null {
        return null;
    }
}
