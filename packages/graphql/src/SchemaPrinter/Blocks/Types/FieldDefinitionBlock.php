<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldDefinition>
 */
class FieldDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        FieldDefinition $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return null;
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $type       = new TypeBlock(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $definition->getType(),
        );
        $args       = new ArgumentsDefinitionList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $definition->args,
        );

        return "{$args}:{$space}{$type}";
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
