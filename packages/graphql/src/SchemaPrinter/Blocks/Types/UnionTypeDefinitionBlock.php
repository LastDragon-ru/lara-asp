<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
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
        UnionType $type,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $type);
    }

    protected function body(int $used): string {
        $indent = $this->indent();
        $space  = $this->space();
        $equal  = "{$space}={$space}";
        $body   = "union{$space}{$this->getName()}";
        $types  = new UnionMemberTypesList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel() + 1,
            $used + mb_strlen($body) + mb_strlen($equal),
            $this->getType()->getTypes(),
        );

        if ($types->isMultiline()) {
            $eol  = $this->eol();
            $body = "{$body}{$eol}{$indent}{$this->indent(1)}={$space}{$types}";
        } else {
            $body = "{$body}{$equal}{$types}";
        }

        return $body;
    }
}
