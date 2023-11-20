<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Argument as GraphQLArgument;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Factory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 *
 * @template TDefinition of Node|GraphQLType|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema
 */
class PrintableBlock extends Block implements NamedBlock {
    private Block $block;

    /**
     * @param TDefinition                      $definition
     * @param (TypeNode&Node)|GraphQLType|null $type
     */
    public function __construct(
        Context $context,
        private object $definition,
        private TypeNode|GraphQLType|null $type = null,
    ) {
        parent::__construct($context);

        $this->block = Factory::create($context, $definition, $type);
    }

    #[Override]
    public function getName(): string {
        $name  = '';
        $block = $this->getBlock();

        if ($block instanceof NamedBlock) {
            $name = $block->getName();
        }

        return $name;
    }

    /**
     * @return TDefinition
     */
    public function getDefinition(): object {
        return $this->definition;
    }

    /**
     * @return (TypeNode&Node)|GraphQLType|null
     */
    public function getType(): TypeNode|GraphQLType|null {
        return $this->type;
    }

    public function getBlock(): Block {
        return $this->block;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return $this->getBlock()->serialize($collector, $level, $used);
    }
}
