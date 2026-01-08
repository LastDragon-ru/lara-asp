<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use InvalidArgumentException;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;
use LastDragon_ru\GlobMatcher\Regex;
use LastDragon_ru\TextParser\Ast\Cursor;
use Override;
use Stringable;

/**
 * Parse/Match glob pattern.
 *
 * @see https://en.wikipedia.org/wiki/Glob_(programming)
 */
readonly class Glob implements Matcher {
    public GlobNode $node;
    public Regex    $regex;
    public Options  $options;

    public function __construct(string $pattern, ?Options $options = null) {
        $this->options = $options ?? new Options();
        $this->node    = $this->parse($this->options, $pattern);
        $this->regex   = $this->regex($this->options, $this->node);
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return $this->regex->match($string);
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
