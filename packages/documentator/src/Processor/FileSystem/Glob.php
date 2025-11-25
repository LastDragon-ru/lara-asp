<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\MatchMode;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Regex;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use SplFileInfo;
use Stringable;

use function assert;
use function implode;

/**
 * @internal
 */
readonly class Glob implements Matcher {
    private ?Regex $regex;

    /**
     * @param list<string> $patterns
     */
    public function __construct(
        private DirectoryPath $root,
        array $patterns,
    ) {
        $this->regex = $this->regex($patterns);
    }

    #[Override]
    public function match(SplFileInfo|Stringable|string $string): bool {
        return (bool) $this->regex?->match($this->path($string));
    }

    public function mismatch(SplFileInfo|Stringable|string $string): bool {
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
        $root    = GlobMatcher::escape((string) $this->root, $options);

        foreach ($patterns as $pattern) {
            if (!isset($regex[$pattern]) && $pattern !== '') {
                $regex[$pattern] = (new GlobMatcher("{$root}{$pattern}", $options))->regex->pattern;
            }
        }

        return $regex !== []
            ? new Regex('(?:'.implode('|', $regex).')', $options->matchMode, $options->matchCase)
            : null;
    }

    protected function path(SplFileInfo|Stringable|string $path): DirectoryPath|FilePath {
        if ($path instanceof DirectoryPath || $path instanceof FilePath) {
            // as is
        } elseif ($path instanceof SplFileInfo) {
            $pathname = $path->getPathname();

            assert($pathname !== '');

            $path = $path->isDir() ? new DirectoryPath($pathname) : new FilePath($pathname);
        } else {
            $pathname = (string) $path;

            assert($pathname !== '');

            $path = new FilePath($pathname);
        }

        return $this->root->resolve($path);
    }
}
