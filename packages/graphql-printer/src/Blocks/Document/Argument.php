<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(ArgumentNode::class)]
class Argument extends Block implements NamedBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private ArgumentNode $argument,
        private TypeNode|Type|null $type = null,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->argument->name->value;
    }

    protected function content(int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        // Convert
        $name     = $this->getName();
        $property = $this->addUsed(
            new PropertyBlock(
                $this->getContext(),
                $name,
                new Value(
                    $this->getContext(),
                    $level + 1,
                    $used,
                    $this->argument->value,
                ),
            ),
        );

        // Statistics
        if ($this->type) {
            $this->addUsedType($this->getTypeName($this->type));
        }

        // Return
        return "{$property}";
    }
}
