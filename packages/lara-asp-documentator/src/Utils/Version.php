<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Composer\Semver\Comparator;

use function mb_substr;
use function preg_match;
use function str_starts_with;

class Version {
    /**
     * @see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
     */
    public const string SEMVER = <<<'SEMVER'
        /^
            (?P<major>0|[1-9]\d*)
            \.
            (?P<minor>0|[1-9]\d*)
            \.
            (?P<patch>0|[1-9]\d*)
            (?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)
                (?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*
            ))?
            (?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$
        /x
        SEMVER;

    public static function isVersion(string $string): bool {
        return static::isSemver($string)
            || (str_starts_with($string, 'v') && static::isSemver(static::normalize($string)));
    }

    public static function isSemver(string $string): bool {
        return (bool) preg_match(static::SEMVER, $string);
    }

    public static function compare(string $a, string $b): int {
        $r = 0;

        if ($a !== $b) {
            $a = static::normalize($a);
            $b = static::normalize($b);
            $r = $a !== $b
                ? (Comparator::greaterThan($a, $b) ? 1 : -1)
                : 0;
        }

        return $r;
    }

    public static function normalize(string $version): string {
        if (str_starts_with($version, 'v')) {
            $version = mb_substr($version, 1);
        } elseif ($version === 'HEAD' || str_starts_with($version, 'dev-')) {
            $version = '9999999-dev';
        } else {
            // empty
        }

        return $version;
    }
}
