<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Value;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 */
class ObjectField extends Block implements NamedBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        private ObjectFieldNode $definition,
        private TypeNode|Type|null $type,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->definition->name->value;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        $level    = $level + (int) ($this->definition->value instanceof StringValueNode);
        $value    = new Value(
            $this->getContext(),
            $this->definition->value,
            $this->type,
        );
        $property = (new PropertyBlock(
            $this->getContext(),
            $this->definition->name->value,
            $value,
        ));

        return $property->serialize($collector, $level, $used);
    }
}
