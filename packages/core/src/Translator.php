<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;

/**
 * Special wrapper around Translator to help translate package's messages.
 */
abstract class Translator implements TranslatorContract {
    public function __construct(
        protected TranslatorContract $translator,
        protected string $package,
        protected string|null $group = 'messages',
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    public function get($key, array $replace = [], $locale = null) {
        return $this->translator->get($this->key($key), $replace, $locale);
    }

    /**
     * @inheritDoc
     */
    public function choice($key, $number, array $replace = [], $locale = null): string {
        return $this->translator->choice($this->key($key), $number, $replace, $locale);
    }

    public function getLocale(): string {
        return $this->translator->getLocale();
    }

    /**
     * @inheritDoc
     */
    public function setLocale($locale) {
        $this->translator->setLocale($locale);
    }

    protected function key(string $key): string {
        return $this->group
            ? "{$this->package}::{$this->group}.{$key}"
            : "{$this->package}::{$key}";
    }
}
