<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\NullValueNode;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\ValueNodeBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldArgument|InputObjectField>
 */
class InputValueDefinitionBlock extends DefinitionBlock {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        FieldArgument|InputObjectField $definition,
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
        $body       = ":{$space}{$type}";

        if ($definition->defaultValueExists()) {
            $prefix = "{$body}{$space}={$space}";
            $value  = $this->addUsed(
                new ValueNodeBlock(
                    $this->getSettings(),
                    $this->getLevel(),
                    $this->getUsed() + mb_strlen($prefix),
                    AST::astFromValue($definition->defaultValue, $definition->getType()) ?? new NullValueNode([]),
                ),
            );
            $body   = "{$prefix}{$value}";
        }

        return $body;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}