<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\MatchMode;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Regex;
use Override;
use Stringable;

use function implode;

/**
 * @internal
 */
readonly class Glob implements Matcher {
    private ?Regex $regex;

    /**
     * @param list<string> $patterns
     */
    public function __construct(array $patterns) {
        $this->regex = $this->regex($patterns);
    }

    #[Override]
    public function match(Stringable|string $string): bool {
        return (bool) $this->regex?->match((string) $string);
    }

    public function mismatch(Stringable|string $string): bool {
        return !$this->match($string);
    }

    /**
     * @param list<string> $patterns
     */
    protected function regex(array $patterns): ?Regex {
        $options = new Options(
            matchMode: MatchMode::Match,
            matchCase: true,
        );
        $regex   = [];

        foreach ($patterns as $pattern) {
            if (!isset($regex[$pattern]) && $pattern !== '') {
                $regex[$pattern] = (new GlobMatcher($pattern, $options))->regex->pattern;
            }
        }

        return $regex !== []
            ? new Regex('(?:'.implode('|', $regex).')', $options->matchMode, $options->matchCase)
            : null;
    }
}
