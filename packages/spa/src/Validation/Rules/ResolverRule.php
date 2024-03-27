<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException;
use Override;

use function array_merge;

class ResolverRule extends Rule implements ValueProvider {
    protected Resolver $resolver;

    public function __construct(Translator $translator, Resolver $resolver) {
        parent::__construct($translator);

        $this->resolver = $resolver;
    }

    // <editor-fold desc="Rule">
    // =========================================================================
    #[Override]
    public function isValid(string $attribute, mixed $value): bool {
        try {
            return (bool) $this->getValue($value);
        } catch (UnresolvedValueException $exception) {
            // no action
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getMessageReplace(): array {
        return array_merge(parent::getMessageReplace(), [
            'resolver' => $this->resolver::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getMessageVariants(): array {
        $defaults   = parent::getMessageVariants();
        $resolver   = $this->resolver::class;
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
    #[Override]
    public function getValue(mixed $value): mixed {
        return $this->resolver->get($value);
    }
    // </editor-fold>
}
