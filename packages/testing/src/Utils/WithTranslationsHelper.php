<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use Illuminate\Translation\Translator;

use function array_merge;

/**
 * @internal
 */
abstract class WithTranslationsHelper extends Translator {
    /**
     * @param array<string,string> $lines
     */
    public static function replaceLines(Translator $translator, string $locale, array $lines): void {
        // Laravel may use translations from JSON files, they have a bigger
        // priority than normal translations. This is why we override them
        // and not normal translations.
        $translator->loaded['*']['*'][$locale] = array_merge($translator->loaded['*']['*'][$locale] ?? [], $lines);
    }
}
