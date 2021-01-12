<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

/**
 * @see \LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedInterface
 */
trait CompositeExpectedImpl {
    private bool $isExpectedFinal = false;

    public function isExpectedFinal(): bool {
        return $this->isExpectedFinal;
    }

    public function setIsExpectedFinal(bool $isExpectedFinal) {
        $this->isExpectedFinal = $isExpectedFinal;

        return $this;
    }
}
