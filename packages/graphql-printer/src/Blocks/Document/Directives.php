<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function json_encode;

/**
 * @internal
 * @extends ListBlock<Directive>
 */
class Directives extends ListBlock {
    /**
     * @param iterable<DirectiveNode>|null $directives
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable|null $directives,
        string|null $deprecationReason = null,
    ) {
        parent::__construct($context, $level, $used);

        $deprecated   = GraphQLDirective::DEPRECATED_NAME;
        $directives ??= [];

        if ($deprecationReason !== null) {
            // todo(graphql): Is there a better way to create directive node?
            if ($deprecationReason !== GraphQLDirective::DEFAULT_DEPRECATION_REASON && $deprecationReason !== '') {
                $reason = json_encode($deprecationReason);
                $this[] = $this->block(Parser::directive("@{$deprecated}(reason: {$reason})"));
            } else {
                $this[] = $this->block(Parser::directive("@{$deprecated}"));
            }
        }

        foreach ($directives as $directive) {
            if ($deprecationReason !== null && $directive->name->value === $deprecated) {
                continue;
            }

            $this[] = $this->block($directive);
        }
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    private function block(DirectiveNode $directive,): Directive {
        return new Directive(
            $this->getContext(),
            $this->getLevel(),
            $this->getUsed(),
            $directive,
        );
    }
}
