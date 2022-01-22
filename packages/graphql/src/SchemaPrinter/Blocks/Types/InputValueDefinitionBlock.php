<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\NullValueNode;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\ValueNodeBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldArgument|InputObjectField>
 */
class InputValueDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        FieldArgument|InputObjectField $definition,
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
        $body       = ":{$space}{$type}";

        if ($definition->defaultValueExists()) {
            $prefix = "{$body}{$space}={$space}";
            $value  = new ValueNodeBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed() + mb_strlen($prefix),
                AST::astFromValue($definition->defaultValue, $definition->getType()) ?? new NullValueNode([]),
            );
            $body   = "{$prefix}{$value}";
        }

        return $body;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
