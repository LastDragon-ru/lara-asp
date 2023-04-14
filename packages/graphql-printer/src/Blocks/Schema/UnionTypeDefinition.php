<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<UnionType>
 */
class UnionTypeDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        UnionType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'union';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $space = $this->space();
        $equal = "={$space}";
        $types = $this->addUsed(
            new UnionMemberTypes(
                $this->getContext(),
                $this->getLevel() + 1,
                $used + mb_strlen($equal),
                $this->getDefinition()->getTypes(),
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
