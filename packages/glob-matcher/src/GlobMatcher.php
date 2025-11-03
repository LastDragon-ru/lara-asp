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
    public Regex   $regex;
    public Options $options;

    public function __construct(
        public string $pattern,
        ?Options $options = null,
    ) {
        $this->options = $options ?? new Options();
        $this->regex   = $this->regex();
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return $this->regex->match($string);
    }

    protected function regex(): Regex {
        $regex    = [];
        $options  = new GlobOptions(
            globstar: $this->options->globstar,
            extended: $this->options->extended,
            hidden  : $this->options->hidden,
        );
        $patterns = $this->options->braces ? new BraceExpander($this->pattern) : [$this->pattern];

        foreach ($patterns as $pattern) {
            $regex[] = (new Glob($pattern, $options))->regex->pattern;
        }

        return new Regex(
            '(?:'.implode('|', $regex).')',
            $this->options->matchMode,
            $this->options->matchCase,
        );
    }
}
