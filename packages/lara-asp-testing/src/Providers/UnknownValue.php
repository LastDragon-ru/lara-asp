<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use LogicException;
use Override;

class UnknownValue extends ExpectedValue {
    public function __construct() {
        parent::__construct(null);
    }

    #[Override]
    public function getValue(): mixed {
        throw new LogicException('The expected value is not provided.');
    }
}
