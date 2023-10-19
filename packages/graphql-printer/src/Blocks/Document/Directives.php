<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function array_unshift;
use function json_encode;

/**
 * @internal
 * @extends ListBlock<Directive, array-key, DirectiveNode>
 */
class Directives extends ListBlock {
    /**
     * @param iterable<array-key, DirectiveNode> $directives
     */
    public function __construct(
        Context $context,
        iterable $directives,
        string|null $deprecationReason = null,
    ) {
        if ($deprecationReason !== null) {
            $list       = [];
            $name       = GraphQLDirective::DEPRECATED_NAME;
            $default    = GraphQLDirective::DEFAULT_DEPRECATION_REASON;
            $replaced   = false;
            $deprecated = null;

            // todo(graphql): Is there a better way to create directive node?
            if ($deprecationReason !== $default && $deprecationReason !== '') {
                $reason     = json_encode($deprecationReason);
                $deprecated = Parser::directive("@{$name}(reason: {$reason})");
            } else {
                $deprecated = Parser::directive("@{$name}");
            }

            foreach ($directives as $key => $directive) {
                if ($directive->name->value === $name) {
                    $list[$key] = $deprecated;
                    $replaced   = true;
                } else {
                    $list[$key] = $directive;
                }
            }

            if (!$replaced) {
                array_unshift($list, $deprecated);
            }

            $directives = $list;
        }

        parent::__construct($context, $directives);
    }

    protected function getSeparator(): string {
        return $this->space();
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDirectives();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineDirectives();
    }

    protected function block(string|int $key, mixed $item): Directive {
        return new Directive($this->getContext(), $item);
    }
}
