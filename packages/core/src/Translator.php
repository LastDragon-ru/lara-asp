<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Closure;
use Countable;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use LastDragon_ru\LaraASP\Core\Utils\Cast;

use function array_splice;
use function array_values;
use function end;

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
     *
     * @param array<string>|string $key
     * @param array<mixed>         $replace
     */
    public function get($key, array $replace = [], $locale = null): string {
        return $this->translate($key, function (string $key) use ($replace, $locale): string {
            return Cast::toString($this->translator->get($this->key($key), $replace, $locale));
        });
    }

    /**
     * @inheritDoc
     *
     * @param array<string>|string       $key
     * @param Countable|int|array<mixed> $number
     * @param array<mixed>               $replace
     */
    public function choice($key, $number, array $replace = [], $locale = null): string {
        return $this->translate($key, function (string $key) use ($number, $replace, $locale): string {
            return $this->translator->choice($this->key($key), $number, $replace, $locale);
        });
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

    /**
     * @param array<string>|string    $variants
     * @param Closure(string): string $callback
     */
    protected function translate(array|string $variants, Closure $callback): string {
        $variants   = array_values((array) $variants);
        $translated = array_splice($variants, -1);
        $translated = (string) end($translated);

        if ($variants) {
            foreach ($variants as $variant) {
                $result = $callback($variant);

                if ($result !== $this->key($variant)) {
                    $translated = $result;
                }
            }
        } else {
            $translated = $callback($translated);
        }

        return $translated;
    }
}