<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<Directive>
 */
class DirectiveDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Directive $definition,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $definition);
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
        $args       = new ArgumentsDefinitionList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $used,
            $definition->args,
        );
        $locations  = new DirectiveLocationsList(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel() + 1,
            $used + $args->getLength(),
            $definition->locations,
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
