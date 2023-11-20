<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

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

    #[Override]
    public function getName(): string {
        return "@{$this->getDefinition()->name->value}";
    }

    public function getDefinition(): DirectiveNode {
        return $this->definition;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isDirectiveAllowed($this->getDefinition()->name->value)) {
            return '';
        }

        // Convert
        $definition = $this->getDefinition();
        $directive  = $this->getName();
        $context    = $this->getContext();
        $args       = new Arguments(
            $context,
            $definition->arguments,
            static function (ArgumentNode $argument) use ($context, $definition): TypeNode|Type|null {
                $name = $argument->name->value;
                $arg  = $context->getDirectiveArgument($definition, $name);
                $type = $arg instanceof InputValueDefinitionNode
                    ? $arg->type
                    : $arg?->getType();

                return $type;
            },
        );

        // Statistics
        $collector->addUsedDirective($directive);

        // Return
        return "{$directive}{$args->serialize($collector, $level, $used)}";
    }
}
