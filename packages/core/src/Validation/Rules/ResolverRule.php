<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use LastDragon_ru\LaraASP\Core\Http\ValueProvider;
use LastDragon_ru\LaraASP\Core\Provider;
use LastDragon_ru\LaraASP\Core\Routing\Resolver;
use LastDragon_ru\LaraASP\Core\Routing\UnresolvedValueException;
use function get_class;

class ResolverRule implements Rule, ValueProvider {
    protected Translator $translator;
    protected Resolver   $resolver;

    public function __construct(Translator $translator, Resolver $resolver) {
        $this->translator = $translator;
        $this->resolver   = $resolver;
    }

    // <editor-fold desc="Rule">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function passes($attribute, $value) {
        try {
            return (bool) $this->getValue($value);
        } catch (UnresolvedValueException $exception) {
            // no action
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function message() {
        $package     = Provider::Package;
        $translation = $this->translator->get("{$package}::validation.resolver", [
            'resolver' => get_class($this->resolver),
        ]);

        return $translation;
    }
    // </editor-fold>

    // <editor-fold desc="ValueProvider">
    // =========================================================================
    public function getValue($value) {
        return $this->resolver->get($value);
    }
    // </editor-fold>
}
