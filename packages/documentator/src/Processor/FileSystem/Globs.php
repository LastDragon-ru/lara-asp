<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\MatchMode;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Regex;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Symfony\Component\Finder\SplFileInfo;

use function implode;

/**
 * @internal
 */
class Globs {
    private ?Regex $regex;

    /**
     * @param list<string> $patterns
     */
    public function __construct(array $patterns) {
        $this->regex = $this->regex($patterns);
    }

    public function isEmpty(): bool {
        return $this->regex === null;
    }

    public function isMatch(DirectoryPath|FilePath|SplFileInfo|string $path): bool {
        return (bool) $this->regex?->isMatch($this->path($path));
    }

    public function isNotMatch(DirectoryPath|FilePath|SplFileInfo|string $path): bool {
        return !$this->isMatch($path);
    }

    /**
     * @param list<string> $patterns
     */
    protected function regex(array $patterns): ?Regex {
        $regex   = [];
        $options = new Options(
            matchMode: MatchMode::Match,
            matchCase: true,
        );

        foreach ($patterns as $pattern) {
            if ($pattern !== '') {
                $regex[] = (new GlobMatcher($pattern, $options))->regex->pattern;
            }
        }

        return $regex !== []
            ? new Regex('(?:'.implode('|', $regex).')', $options->matchMode, $options->matchCase)
            : null;
    }

    protected function path(DirectoryPath|FilePath|SplFileInfo|string $path): string {
        return $path instanceof SplFileInfo
            ? $path->getRelativePathname()
            : (string) $path;
    }
}
