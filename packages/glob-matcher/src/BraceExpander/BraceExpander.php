<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander;

use InvalidArgumentException;
use IteratorAggregate;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Parser;
use LastDragon_ru\TextParser\Ast\Cursor;
use Override;
use Traversable;

/**
 * Expands Bash Brace Expansion.
 *
 * @see https://www.gnu.org/software/bash/manual/html_node/Brace-Expansion.html
 *
 * @implements IteratorAggregate<int, string>
 */
readonly class BraceExpander implements IteratorAggregate {
    public BraceExpansionNode $node;

    public function __construct(string $pattern) {
        $this->node = $this->parse($pattern);
    }

    private function parse(string $pattern): BraceExpansionNode {
        $node = (new Parser())->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid brace expansion string.');
        }

        return $node;
    }

    #[Override]
    public function getIterator(): Traversable {
        $cursor   = new Cursor($this->node);
        $iterator = $this->node::toIterable($cursor);

        foreach ($iterator as $string) {
            yield $string;
        }
    }
}
