<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Translation\Translator as TranslatorImpl;
use LastDragon_ru\LaraASP\Testing\Exceptions\TranslatorUnsupported;

use function is_callable;

/**
 * Allows replacing translation strings for Laravel.
 *
 * @phpstan-type Translations         array<string,array<string,string>>
 * @phpstan-type TranslationsCallback callable(static, string $currentLocale, string $fallbackLocale): Translations
 * @phpstan-type TranslationsFactory  TranslationsCallback|Translations|null
 */
trait WithTranslations {
    abstract protected function app(): Application;

    /**
     * @param TranslationsFactory $translations
     */
    public function setTranslations(callable|array|null $translations): void {
        // Translator
        $translator = $this->app()->make(Translator::class);

        if (!($translator instanceof TranslatorImpl)) {
            throw new TranslatorUnsupported($translator::class);
        }

        // Prepare
        if (is_callable($translations)) {
            $translations = $translations($this, $translator->getLocale(), $translator->getFallback());
        }

        // Replace
        foreach ((array) $translations as $locale => $lines) {
            WithTranslationsHelper::replaceLines($translator, $locale, $lines);
        }
    }
}
