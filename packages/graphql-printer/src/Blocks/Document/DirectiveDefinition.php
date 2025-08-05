<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Type\Definition\Directive;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

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
        DirectiveDefinitionNode|Directive $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'directive';
    }

    #[Override]
    protected function name(): string {
        return '@'.parent::name();
    }

    #[Override]
    protected function arguments(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $arguments  = new ArgumentsDefinition(
            $this->getContext(),
            $definition instanceof DirectiveDefinitionNode
                ? $definition->arguments
                : $definition->args,
        );

        return $arguments;
    }

    #[Override]
    protected function body(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $locations  = new DirectiveLocations(
            $this->getContext(),
            $definition->locations,
            $multiline,
            $definition instanceof DirectiveDefinitionNode
                ? $definition->repeatable
                : $definition->isRepeatable,
        );

        return $locations;
    }
}
