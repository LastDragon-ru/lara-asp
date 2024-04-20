<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 */
#[GraphQLAstNode(ArgumentNode::class)]
class Argument extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        private ArgumentNode $argument,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->argument->name->value;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        // Convert
        $name     = $this->getName();
        $property = new PropertyBlock(
            $this->getContext(),
            $name,
            new Value(
                $this->getContext(),
                $this->argument->value,
                $this->type,
            ),
        );

        // Return
        return $property->serialize($collector, $level, $used);
    }
}
