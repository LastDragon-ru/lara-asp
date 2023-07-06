<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use GraphQL\Language\AST\Node;
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

/**
 * @internal
 *
 * @template TDefinition of Node|GraphQLType|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema
 */
class PrintableBlock extends Block implements NamedBlock {
    private Block $block;

    /**
     * @param TDefinition $definition
     */
    public function __construct(
        Context $context,
        private object $definition,
    ) {
        parent::__construct($context);

        $this->block = $this->getDefinitionBlock($definition);
    }

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

    public function getBlock(): Block {
        return $this->block;
    }

    protected function content(Collector $collector, int $level, int $used): string {
        return $this->getBlock()->content($collector, $level, $used);
    }

    /**
     * @param TDefinition $definition
     */
    private function getDefinitionBlock(object $definition): Block {
        return Factory::create($this->getContext(), $definition);
    }
}
