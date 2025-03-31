<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Symfony\Component\Finder\Glob;

use function array_filter;
use function array_map;
use function implode;
use function mb_substr;
use function preg_match;

/**
 * @internal
 */
class Globs {
    public readonly ?string $regexp;

    public function __construct(
        /**
         * @var array<array-key, string>
         */
        public readonly array $globs,
    ) {
        $this->regexp = $this->toRegexp($this->globs);
    }

    public function isMatch(FilePath $file): bool {
        return $this->regexp !== null && preg_match($this->regexp, (string) $file) > 0;
    }

    /**
     * @param array<array-key, string> $globs
     */
    protected function toRegexp(array $globs): ?string {
        $delimiter = '#';
        $regexps   = array_map(
            static function (string $glob) use ($delimiter): string {
                $regexp = Glob::toRegex($glob, delimiter: $delimiter);
                $regexp = mb_substr($regexp, 1, -1);

                return $regexp;
            },
            $globs,
        );
        $regexps   = array_filter($regexps, static fn ($s) => $s !== '');
        $regexp    = $regexps !== []
            ? $delimiter.'('.implode(')|(', $regexps).')'.$delimiter.'u'
            : null;

        return $regexp;
    }
}
