<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

use Closure;

/**
 * @experimental
 */
class MockedProperty {
    /**
     * @param Closure(object): void $setter
     */
    public function __construct(
        protected Closure $setter,
    ) {
        // empty
    }

    public function value(object $value): static {
        ($this->setter)($value);

        return $this;
    }
}
