<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<Directive>
 */
class DirectiveDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Settings $settings,
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
        $definition  = $this->getDefinition();
        $eol         = $this->eol();
        $space       = $this->space();
        $indent      = $this->indent();
        $repeatable  = 'repeatable';
        $used        = $used + mb_strlen($repeatable) + 2 * mb_strlen($space);
        $args        = $this->addUsed(
            new ArgumentsDefinitionList(
                $this->getSettings(),
                $this->getLevel(),
                $used,
                $definition->args,
            ),
        );
        $locations   = $this->addUsed(
            new DirectiveLocationsList(
                $this->getSettings(),
                $this->getLevel() + 1,
                $used + $args->getLength(),
                $definition->locations,
                $args->isMultiline(),
            ),
        );
        $isMultiline = $args->isMultiline() || $locations->isMultiline();
        $content     = "{$args}";

        if ($isMultiline) {
            $content .= "{$eol}{$indent}";
        } else {
            $content .= "{$space}";
        }

        if ($definition->isRepeatable) {
            $content .= "{$repeatable}{$space}";
        }

        $content .= "{$locations}";

        return $content;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
