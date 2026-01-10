<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use Illuminate\Translation\Translator;

use function array_merge;
use function assert;
use function is_array;

/**
 * @internal
 */
abstract class WithTranslationsHelper extends Translator {
    /**
     * @param array<string,string> $lines
     */
    public static function replaceLines(Translator $translator, string $locale, array $lines): void {
        // We need to load the locale first
        $translator->load('*', '*', $locale);

        assert(
            isset($translator->loaded['*'])
            && is_array($translator->loaded['*'])
            && isset($translator->loaded['*']['*'])
            && is_array($translator->loaded['*']['*'])
            && isset($translator->loaded['*']['*'][$locale])
            && is_array($translator->loaded['*']['*'][$locale]),
        );

        // Laravel may use translations from JSON files, they have a bigger
        // priority than normal translations. This is why we override them
        // and not normal translations.
        $translator->loaded['*']['*'][$locale] = array_merge($translator->loaded['*']['*'][$locale] ?? [], $lines);
    }
}
