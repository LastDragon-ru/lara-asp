<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException;

use function array_merge;
use function get_class;

class ResolverRule extends Rule implements ValueProvider {
    protected Resolver $resolver;

    public function __construct(Translator $translator, Resolver $resolver) {
        parent::__construct($translator);

        $this->resolver = $resolver;
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

    protected function getMessageReplace(): array {
        return array_merge(parent::getMessageReplace(), [
            'resolver' => get_class($this->resolver),
        ]);
    }

    protected function getMessageVariants(): array {
        $defaults   = parent::getMessageVariants();
        $resolver   = get_class($this->resolver);
        $variants   = [];
        $customized = [];

        foreach ($defaults as $variant) {
            $variants[]   = "{$variant}.default";
            $customized[] = "{$variant}.{$resolver}";
        }

        return array_merge($customized, $variants);
    }
    // </editor-fold>

    // <editor-fold desc="ValueProvider">
    // =========================================================================
    public function getValue(mixed $value): mixed {
        return $this->resolver->get($value);
    }
    // </editor-fold>
}
