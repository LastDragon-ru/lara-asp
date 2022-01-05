<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Nodes;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\ListBlockList;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\ObjectBlockList;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;

class Value extends Block {
    /**
     * @param ValueNode&Node $node
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        protected ValueNode $node,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function content(): string {
        $content = '';

        if ($this->node instanceof ListValueNode) {
            $content = new ListBlockList($this->getSettings(), $this->getLevel(), $this->getUsed());

            foreach ($this->node->values as $value) {
                $content[] = new Value($this->getSettings(), $this->getLevel() + 1, $this->getUsed(), $value);
            }
        } elseif ($this->node instanceof ObjectValueNode) {
            $content = new ObjectBlockList($this->getSettings(), $this->getLevel(), $this->getUsed());

            foreach ($this->node->fields as $field) {
                $content[$field->name->value] = new Value(
                    $this->getSettings(),
                    $this->getLevel() + 1 + (int) ($field->value instanceof StringValueNode),
                    $this->getUsed(),
                    $field->value,
                );
            }
        } elseif ($this->node instanceof StringValueNode) {
            $content = $this->node->block
                ? new StringBlock($this->getSettings(), $this->getLevel(), 0, $this->node->value, true)
                : Printer::doPrint($this->node);
        } else {
            $content = Printer::doPrint($this->node);
        }

        return (string) $content;
    }
}
