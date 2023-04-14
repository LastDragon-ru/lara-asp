<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 */
class ArgumentNodeBlock extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private DirectiveNode $node,
        private ArgumentNode $argument,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->getArgument()->name->value;
    }

    public function getNode(): DirectiveNode {
        return $this->node;
    }

    public function getArgument(): ArgumentNode {
        return $this->argument;
    }

    protected function content(): string {
        // Print?
        if ($this->getSettings()->getTypeFilter()) {
            // AST Node doesn't contain type of argument, but it can be
            // determined by directive definition.
            $directive  = $this->getNode()->name->value;
            $definition = $this->getContext()->getDirective($directive);
            $name       = $this->getName();
            $type       = null;

            foreach ($definition->args ?? [] as $arg) {
                if ($arg->name === $name) {
                    $type = $arg->getType();
                    break;
                }
            }

            if ($type && !$this->isTypeAllowed($type)) {
                return '';
            }
        }

        // Convert
        $name     = $this->getName();
        $argument = $this->getArgument();
        $property = $this->addUsed(
            new PropertyBlock(
                $this->getContext(),
                $name,
                new ValueNodeBlock(
                    $this->getContext(),
                    $this->getLevel() + 1,
                    $this->getUsed(),
                    $argument->value,
                ),
            ),
        );

        // Return
        return "{$property}";
    }
}
