<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(DirectiveNode::class)]
class Directive extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        private DirectiveNode $definition,
    ) {
        parent::__construct($context);
    }

    public function getName(): string {
        return "@{$this->getDefinition()->name->value}";
    }

    public function getDefinition(): DirectiveNode {
        return $this->definition;
    }

    protected function content(int $level, int $used): string {
        // Print?
        if (!$this->isDirectiveAllowed($this->getDefinition()->name->value)) {
            return '';
        }

        // Convert
        $definition = $this->getDefinition();
        $directive  = $this->getName();
        $context    = $this->getContext();
        $args       = new Arguments($context);

        foreach ($definition->arguments as $node) {
            $name        = $node->name->value;
            $arg         = $context->getDirectiveArgument($definition, $name);
            $type        = $arg instanceof InputValueDefinitionNode
                ? $arg->type
                : $arg?->getType();
            $args[$name] = new Argument(
                $context,
                $node,
                $type,
            );
        }

        // Statistics
        $this->addUsed($args);
        $this->addUsedDirective($directive);

        // Return
        return "{$directive}{$args->serialize($level, $used)}";
    }
}
