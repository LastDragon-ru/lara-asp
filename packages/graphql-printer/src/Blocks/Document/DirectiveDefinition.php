<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends DefinitionBlock<DirectiveDefinitionNode|Directive>
 */
#[GraphQLAstNode(DirectiveDefinitionNode::class)]
#[GraphQLDefinition(Directive::class)]
class DirectiveDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        DirectiveDefinitionNode|Directive $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function prefix(): ?string {
        return 'directive';
    }

    public function name(): string {
        return '@'.parent::name();
    }

    protected function arguments(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $arguments  = new ArgumentsDefinition(
            $this->getContext(),
            $level,
            $used,
            $definition instanceof DirectiveDefinitionNode
                ? $definition->arguments
                : $definition->args,
        );

        return $arguments;
    }

    protected function body(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $locations  = new DirectiveLocations(
            $this->getContext(),
            $level + 1,
            $used,
            $definition->locations,
            $multiline,
            $definition instanceof DirectiveDefinitionNode
                ? $definition->repeatable
                : $definition->isRepeatable,
        );

        return $locations;
    }
}
