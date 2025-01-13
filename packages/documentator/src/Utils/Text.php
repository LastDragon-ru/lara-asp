<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use function hash;
use function implode;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function mb_trim;
use function min;
use function pathinfo;
use function preg_replace;
use function preg_split;
use function str_repeat;
use function str_replace;
use function str_starts_with;

use const PATHINFO_FILENAME;
use const PHP_INT_MAX;

class Text {
    public static function hash(string $text): string {
        return hash('xxh3', $text);
    }

    public static function setEol(string $text, ?string $eol = null): string {
        return preg_replace('/\R/u', $eol ?? "\n", $text) ?? $text;
    }

    /**
     * @param int<0, max> $level
     */
    public static function setPadding(string $text, int $level, string $padding = ' ', int &$cuts = 0): string {
        $trimmed = mb_rtrim($padding);
        $prefix  = str_repeat($padding, $level);
        $length  = mb_strlen($padding);
        $lines   = static::getLines($text);
        $cut     = PHP_INT_MAX;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $trims = 0;

            while (
                $line !== ''
                && (str_starts_with($line, $padding) || ($trimmed !== '' && str_starts_with($line, $trimmed)))
            ) {
                $line = mb_substr($line, $length);

                $trims++;
            }

            $cut = min($cut, $trims * $length);
        }

        foreach ($lines as $i => $line) {
            $lines[$i] = mb_rtrim($prefix.mb_substr($line, $cut));
        }

        $text = implode("\n", $lines);
        $cuts = (int) ($cut / $length);

        return $text;
    }

    /**
     * @return list<string>
     */
    public static function getLines(string $text): array {
        $lines = preg_split('/\R/u', $text);
        $lines = $lines !== false ? $lines : [];

        return  $lines;
    }

    public static function getPathTitle(string $path): string {
        $title = pathinfo($path, PATHINFO_FILENAME);
        $title = str_replace(['_', '.'], ' ', $title);
        $title = (string) preg_replace('/(\p{Ll})(\p{Lu})/u', '$1 $2', $title);
        $title = (string) preg_replace('/\s+/u', ' ', $title);
        $title = mb_trim($title);

        return $title;
    }
}
