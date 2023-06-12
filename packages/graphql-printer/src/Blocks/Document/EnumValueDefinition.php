<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 * @extends DefinitionBlock<GraphQLEnumValueDefinition>
 */
#[GraphQLDefinition(GraphQLEnumValueDefinition::class)]
class EnumValueDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        GraphQLEnumValueDefinition $value,
    ) {
        parent::__construct($context, $level, $used, $value);
    }

    protected function type(): string|null {
        return null;
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
