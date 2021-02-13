<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

class ExpectedValue {
    private mixed $value;

    public function __construct(mixed $value) {
        $this->value = $value;
    }

    public function getValue(): mixed {
        return $this->value instanceof ExpectedValueProvider
            ? $this->value->getExpectedValue()
            : $this->value;
    }
}
