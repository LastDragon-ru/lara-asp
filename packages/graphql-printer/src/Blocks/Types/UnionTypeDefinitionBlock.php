<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<UnionType>
 */
class UnionTypeDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        UnionType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
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
            new UnionMemberTypesList(
                $this->getSettings(),
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
