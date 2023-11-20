<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\UnionMemberTypes;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 *
 * @template TType of UnionTypeDefinitionNode|UnionTypeExtensionNode|UnionType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class UnionDefinitionBlock extends DefinitionBlock implements TypeDefinitionBlock {
    public function __construct(
        Context $context,
        UnionTypeDefinitionNode|UnionTypeExtensionNode|UnionType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $types      = new UnionMemberTypes(
            $this->getContext(),
            $definition instanceof UnionType
                ? $definition->getTypes()
                : $definition->types,
            false,
        );

        return $types;
    }
}
