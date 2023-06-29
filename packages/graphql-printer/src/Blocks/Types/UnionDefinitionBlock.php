<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\UnionMemberTypes;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of UnionTypeDefinitionNode|UnionTypeExtensionNode|UnionType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class UnionDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        UnionTypeDefinitionNode|UnionTypeExtensionNode|UnionType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $equal      = "={$space}";
        $types      = $this->addUsed(
            new UnionMemberTypes(
                $this->getContext(),
                $this->getLevel() + 1,
                $used + mb_strlen($equal),
                $definition instanceof UnionType
                    ? $definition->getTypes()
                    : $definition->types,
            ),
        );

        if ($types->isMultiline()) {
            $eol    = $this->eol();
            $indent = $this->indent($this->getLevel() + 1);
            $types  = "={$eol}{$indent}{$types}";
        } else {
            $types = "{$equal}{$types}";
        }

        return $types;
    }
}
