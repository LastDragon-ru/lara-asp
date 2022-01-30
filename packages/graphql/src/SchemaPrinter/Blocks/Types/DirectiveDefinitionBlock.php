<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<Directive>
 */
class DirectiveDefinitionBlock extends DefinitionBlock {
    public function __construct(
        BlockSettings $settings,
        int $level,
        int $used,
        Directive $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'directive';
    }

    protected function name(): string {
        return '@'.parent::name();
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $eol        = $this->eol();
        $space      = $this->space();
        $indent     = $this->indent();
        $repeatable = 'repeatable';
        $used       = $used + mb_strlen($repeatable) + 2 * mb_strlen($space);
        $args       = $this->addUsed(
            new ArgumentsDefinitionList(
                $this->getSettings(),
                $this->getLevel(),
                $used,
                $definition->args,
            ),
        );
        $locations  = $this->addUsed(
            new DirectiveLocationsList(
                $this->getSettings(),
                $this->getLevel() + 1,
                $used + $args->getLength(),
                $definition->locations,
            ),
        );
        $content    = "{$args}";

        if ($args->isMultiline()) {
            $content .= "{$eol}{$indent}";
        }

        if ($definition->isRepeatable) {
            if (!$args->isMultiline()) {
                $content .= "{$space}";
            }

            $content .= "{$repeatable}{$space}{$locations}";
        } else {
            if (!$args->isMultiline()) {
                $content .= "{$space}";
            }

            $content .= "{$locations}";
        }

        return $content;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
