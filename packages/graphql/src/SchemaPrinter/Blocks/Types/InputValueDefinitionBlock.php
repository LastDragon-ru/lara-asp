<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\ValueNodeBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldArgument>
 */
class InputValueDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        FieldArgument $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $definition);
    }

    protected function body(int $used): string {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $name       = $this->getName();
        $type       = new TypeBlock(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $definition->getType(),
        );
        $body       = "{$name}:{$space}{$type}";

        if ($definition->defaultValueExists()) {
            $value = new ValueNodeBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                AST::astFromValue($definition->defaultValue, $definition->getType()),
            );
            $body  = "{$body}{$space}={$space}{$value}";
        }

        return $body;
    }
}
