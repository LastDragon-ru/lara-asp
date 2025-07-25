<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use Override;
use Stringable;

use function preg_match;

readonly class Regex implements Stringable {
    protected string $regex;

    public function __construct(
        public string $pattern,
        public MatchMode $matchMode = MatchMode::Match,
        public bool $matchCase = true,
    ) {
        $this->regex = $this->regex();
    }

    public function isMatch(string $string): bool {
        return (bool) preg_match($this->regex, $string);
    }

    protected function regex(): string {
        $prefix = match ($this->matchMode) {
            MatchMode::Match, MatchMode::Starts => '^',
            default                             => '',
        };
        $suffix = match ($this->matchMode) {
            MatchMode::Match, MatchMode::Ends => '$',
            default                           => '',
        };
        $flags = $this->matchCase ? '' : 'i';
        $regex = "#{$prefix}{$this->pattern}{$suffix}#us{$flags}";

        return $regex;
    }

    #[Override]
    public function __toString(): string {
        return $this->regex;
    }
}
