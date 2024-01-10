<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context as ContextContract;
use Override;

/**
 * @internal
 */
class Context implements ContextContract {
    /**
     * @var array<class-string, object|null>
     */
    private array $context = [];

    public function __construct() {
        // empty
    }

    #[Override]
    public function has(string $key): bool {
        return isset($this->context[$key]) && $this->context[$key] instanceof $key;
    }

    #[Override]
    public function get(string $key): mixed {
        return isset($this->context[$key]) && $this->context[$key] instanceof $key
            ? $this->context[$key]
            : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function override(array $context): static {
        $overridden = clone $this;

        foreach ($context as $key => $value) {
            $overridden->context[$key] = $value;
        }

        return $overridden;
    }
}
