<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use InvalidArgumentException;
use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Parser\Parser;

use function preg_match;

/**
 * Yet another glob implementation.
 *
 * Why? Because I cannot find a library that supports "globstar" that is needed
 * to simplify patterns.
 */
readonly class Glob {
    public GlobNode $node;
    public string   $regex;

    public function __construct(string $pattern, ?Options $options = null) {
        $options   ??= new Options();
        $this->node  = $this->parse($options, $pattern);
        $this->regex = $this->regex($options, $this->node);
    }

    public function isMatch(string $path): bool {
        return (bool) preg_match($this->regex, $path);
    }

    private function parse(Options $options, string $pattern): GlobNode {
        $node = (new Parser($options))->parse($pattern);

        if ($node === null) {
            throw new InvalidArgumentException('The `$pattern` is not a valid glob.');
        }

        return $node;
    }

    private function regex(Options $options, GlobNode $node): string {
        $prefix = match ($options->matchMode) {
            MatchMode::Match, MatchMode::Starts => '^',
            default                             => '',
        };
        $suffix = match ($options->matchMode) {
            MatchMode::Match, MatchMode::Ends => '$',
            default                           => '',
        };
        $flags = $options->matchCase ? '' : 'i';
        $regex = $node::toRegex($options, new Cursor($node));
        $regex = "#{$prefix}(?:{$regex}){$suffix}#us{$flags}";

        return $regex;
    }
}
