<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldDefinition>
 */
class FieldDefinitionBlock extends DefinitionBlock {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        FieldDefinition $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return null;
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $type       = $this->addUsed(
            new TypeBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition->getType(),
            ),
        );
        $args       = $this->addUsed(
            new ArgumentsDefinitionList(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition->args,
            ),
        );

        return "{$args}:{$space}{$type}";
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
