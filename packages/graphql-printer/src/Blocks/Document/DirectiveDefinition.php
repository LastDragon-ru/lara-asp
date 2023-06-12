<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<Directive>
 */
#[GraphQLDefinition(Directive::class)]
class DirectiveDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        Directive $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
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
            new ArgumentsDefinition(
                $this->getContext(),
                $this->getLevel(),
                $used,
                $definition->args,
            ),
        );
        $locations   = $this->addUsed(
            new DirectiveLocations(
                $this->getContext(),
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
