<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Closure;
use Countable;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use LastDragon_ru\LaraASP\Core\Utils\Cast;

use function array_splice;
use function end;
use function str_contains;

/**
 * Special wrapper around {@see TranslatorContract} to help translate package's messages.
 */
abstract class Translator {
    public function __construct(
        protected TranslatorContract $translator,
    ) {
        // empty
    }

    /**
     * Should return the name of the package.
     */
    abstract protected function getName(): string;

    /**
     * @param list<string>|string  $key
     * @param array<string, mixed> $replace
     */
    public function get(array|string $key, array $replace = [], ?string $locale = null): string {
        return $this->translate($key, function (string $key) use ($replace, $locale): string {
            return Cast::toString($this->translator->get($this->key($key), $replace, $locale));
        });
    }

    /**
     * @param list<string>|string                   $key
     * @param Countable|int|array<array-key, mixed> $number
     * @param array<string, mixed>                  $replace
     */
    public function choice(
        array|string $key,
        Countable|array|int $number,
        array $replace = [],
        ?string $locale = null,
    ): string {
        return $this->translate($key, function (string $key) use ($number, $replace, $locale): string {
            return $this->translator->choice($this->key($key), $number, $replace, $locale);
        });
    }

    public function getLocale(): string {
        return $this->translator->getLocale();
    }

    public function setLocale(string $locale): static {
        $this->translator->setLocale($locale);

        return $this;
    }

    protected function key(string $key): string {
        if (!str_contains($key, '::')) {
            $key = "{$this->getName()}::messages.{$key}";
        }

        return $key;
    }

    /**
     * @param list<string>|string     $variants
     * @param Closure(string): string $callback
     */
    protected function translate(array|string $variants, Closure $callback): string {
        $variants   = (array) $variants;
        $translated = array_splice($variants, -1);
        $translated = (string) end($translated);

        if ($variants !== []) {
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
