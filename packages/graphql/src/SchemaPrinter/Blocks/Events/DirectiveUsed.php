<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events;

use GraphQL\Language\AST\DirectiveNode;

/**
 * @internal
 */
class DirectiveUsed implements Event {
    public function __construct(
        public DirectiveNode $directive,
    ) {
        // empty
    }
}
