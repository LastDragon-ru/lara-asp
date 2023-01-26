<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;
use Traversable;

use function json_encode;

/**
 * @internal
 * @extends ListBlock<DirectiveNodeBlock>
 */
class DirectiveNodeList extends ListBlock {
    /**
     * @param Traversable<DirectiveNode>|array<DirectiveNode> $directives
     */
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        Traversable|array|null $directives,
        string|null $deprecationReason = null,
    ) {
        parent::__construct($settings, $level, $used);

        $deprecated   = Directive::DEPRECATED_NAME;
        $directives ??= [];

        if ($deprecationReason) {
            // todo(graphql): Is there a better way to create directive node?
            if ($deprecationReason !== Directive::DEFAULT_DEPRECATION_REASON) {
                $reason = json_encode($deprecationReason);
                $this[] = $this->block(Parser::directive("@{$deprecated}(reason: {$reason})"));
            } else {
                $this[] = $this->block(Parser::directive("@{$deprecated}"));
            }
        }

        foreach ($directives as $directive) {
            if ($deprecationReason && $directive->name->value === $deprecated) {
                continue;
            }

            $this[] = $this->block($directive);
        }
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function isValidBlock(Block $value): bool {
        return parent::isValidBlock($value)
            && $this->getSettings()->isDirectiveAllowed($value->getNode()->name->value);
    }

    private function block(DirectiveNode $directive,): DirectiveNodeBlock {
        return new DirectiveNodeBlock(
            $this->getSettings(),
            $this->getLevel(),
            $this->getUsed(),
            $directive,
        );
    }
}
