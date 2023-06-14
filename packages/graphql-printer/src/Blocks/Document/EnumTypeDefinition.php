<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\PhpEnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<EnumTypeDefinitionNode|EnumType>
 */
#[GraphQLAstNode(EnumTypeDefinitionNode::class)]
#[GraphQLDefinition(EnumType::class)]
#[GraphQLDefinition(PhpEnumType::class)]
class EnumTypeDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        EnumTypeDefinitionNode|EnumType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string {
        return 'enum';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $values     = $this->addUsed(
            new EnumValuesDefinition(
                $this->getContext(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $definition instanceof EnumType
                    ? $definition->getValues()
                    : $definition->values,
            ),
        );

        return $values;
    }
}
