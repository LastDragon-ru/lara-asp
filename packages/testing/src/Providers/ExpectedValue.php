<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

class ExpectedValue {
    /**
     * @var mixed
     */
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value instanceof ExpectedValueProvider
            ? $this->value->getExpectedValue()
            : $this->value;
    }
}
