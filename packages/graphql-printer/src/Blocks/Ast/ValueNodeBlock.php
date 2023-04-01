<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\StringBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

/**
 * @internal
 */
class ValueNodeBlock extends Block {
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
        $content  = '';
        $settings = $this->getSettings();
        $level    = $this->getLevel();
        $used     = $this->getUsed();

        if ($this->node instanceof ListValueNode) {
            $content = new ListValueList($settings, $level, $used);

            foreach ($this->node->values as $value) {
                $content[] = new self($settings, $level + 1, $used, $value);
            }
        } elseif ($this->node instanceof ObjectValueNode) {
            $content = new ObjectValueList($settings, $level, $used);

            foreach ($this->node->fields as $field) {
                $name           = $field->name->value;
                $content[$name] = new PropertyBlock(
                    $settings,
                    $name,
                    new self(
                        $settings,
                        $level + 1 + (int) ($field->value instanceof StringValueNode),
                        $used,
                        $field->value,
                    ),
                );
            }
        } elseif ($this->node instanceof StringValueNode) {
            $content = $this->node->block
                ? new StringBlock($settings, $level, 0, $this->node->value)
                : Printer::doPrint($this->node);
        } else {
            $content = Printer::doPrint($this->node);
        }

        return (string) $this->addUsed($content);
    }
}
