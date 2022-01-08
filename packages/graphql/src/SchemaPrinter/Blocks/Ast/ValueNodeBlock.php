<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\ListBlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Property;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\StringBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

class ValueNodeBlock extends Block {
    /**
     * @param ValueNode&Node $node
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        protected ValueNode $node,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    protected function content(): string {
        $content    = '';
        $dispatcher = $this->getDispatcher();
        $settings   = $this->getSettings();
        $level      = $this->getLevel();
        $used       = $this->getUsed();

        if ($this->node instanceof ListValueNode) {
            $content = new ListBlockList($dispatcher, $settings, $level, $used);

            foreach ($this->node->values as $value) {
                $content[] = new ValueNodeBlock($dispatcher, $settings, $level + 1, $used, $value);
            }
        } elseif ($this->node instanceof ObjectValueNode) {
            $content = new ObjectBlockList($dispatcher, $settings, $level, $used);

            foreach ($this->node->fields as $field) {
                $name           = $field->name->value;
                $content[$name] = new Property(
                    $dispatcher,
                    $settings,
                    $name,
                    new ValueNodeBlock(
                        $dispatcher,
                        $settings,
                        $level + 1 + (int) ($field->value instanceof StringValueNode),
                        $used,
                        $field->value,
                    ),
                );
            }
        } elseif ($this->node instanceof StringValueNode) {
            $content = $this->node->block
                ? new StringBlock($dispatcher, $settings, $level, 0, $this->node->value)
                : Printer::doPrint($this->node);
        } else {
            $content = Printer::doPrint($this->node);
        }

        return (string) $content;
    }
}
