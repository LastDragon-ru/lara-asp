<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser;

class Utils {
    /**
     * @param iterable<mixed, string> $iterable
     */
    public static function toString(iterable $iterable, string $separator = ''): string {
        $string = '';
        $first  = true;

        foreach ($iterable as $value) {
            $string .= ($first ? '' : $separator).$value;
            $first   = false;
        }

        return $string;
    }
}
