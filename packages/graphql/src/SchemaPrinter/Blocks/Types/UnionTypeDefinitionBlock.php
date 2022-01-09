<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<UnionType>
 */
class UnionTypeDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        UnionType $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'union';
    }

    protected function body(int $used): Block|string|null {
        $space = $this->space();
        $equal = "{$space}={$space}";
        $types = new UnionMemberTypesList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel() + 1,
            $used + mb_strlen($equal),
            $this->getDefinition()->getTypes(),
        );

        if ($types->isMultiline()) {
            $eol    = $this->eol();
            $indent = $this->indent($this->getLevel() + 1);
            $body   = "{$space}={$eol}{$indent}{$types}";
        } else {
            $body = "{$equal}{$types}";
        }

        return $body;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
