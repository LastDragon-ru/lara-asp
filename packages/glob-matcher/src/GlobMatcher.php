<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\GlobMatcher\Glob\Options as GlobOptions;
use Override;
use Stringable;

use function implode;

readonly class GlobMatcher implements Matcher {
    public Regex $regex;

    public function __construct(
        public string $pattern,
        protected ?Options $options = null,
    ) {
        $this->regex = $this->regex();
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return $this->regex->match($string);
    }

    protected function regex(): Regex {
        $regex    = [];
        $default  = new Options();
        $options  = new GlobOptions(
            globstar: $this->options->globstar ?? $default->globstar,
            extended: $this->options->extended ?? $default->extended,
            hidden  : $this->options->hidden ?? $default->hidden,
        );
        $patterns = ($this->options->braces ?? $default->braces) ? new BraceExpander($this->pattern) : [$this->pattern];

        foreach ($patterns as $pattern) {
            $regex[] = (new Glob($pattern, $options))->regex->pattern;
        }

        return new Regex(
            '(?:'.implode('|', $regex).')',
            $this->options->matchMode ?? MatchMode::Match,
            $this->options->matchCase ?? true,
        );
    }
}
