<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Spa\Package;
use ReflectionClass;

use function preg_replace;

abstract class Rule implements RuleContract {
    protected Translator $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    // <editor-fold desc="Rule">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function message() {
        $replace     = $this->getMessageReplace();
        $variants    = $this->getMessageVariants();
        $translation = (new Collection($variants))
            ->mapWithKeys(function (string $variant) use ($replace) {
                return [$variant => $this->translator->get($variant, $replace)];
            })
            ->first(static function (string $value, string $key) {
                return $key !== $value;
            });

        return $translation;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    protected function getMessageReplace(): array {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMessageVariants(): array {
        $name    = Str::snake((new ReflectionClass($this))->getShortName());
        $name    = preg_replace('/_rule$/', '', $name);
        $package = Package::Name;

        return [
            "validation.{$package}.{$name}",  // application
            "{$package}::validation.{$name}", // package
        ];
    }
    // </editor-fold>
}
