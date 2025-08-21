<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use InvalidArgumentException;
use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;
use LastDragon_ru\GlobMatcher\Regex;

/**
 * Parse/Match glob pattern.
 *
 * @see https://en.wikipedia.org/wiki/Glob_(programming)
 */
readonly class Glob {
    public GlobNode $node;
    public Regex    $regex;

    public function __construct(string $pattern, ?Options $options = null) {
        $options   ??= new Options();
        $this->node  = $this->parse($options, $pattern);
        $this->regex = $this->regex($options, $this->node);
    }

    public function isMatch(string $path): bool {
        return $this->regex->isMatch($path);
    }

    private function parse(Options $options, string $pattern): GlobNode {
        $node = (new Parser($options))->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid glob.');
        }

        return $node;
    }

    private function regex(Options $options, GlobNode $node): Regex {
        $regex = $node::toRegex($options, new Cursor($node));
        $regex = new Regex($regex, $options->matchMode, $options->matchCase);

        return $regex;
    }
}
