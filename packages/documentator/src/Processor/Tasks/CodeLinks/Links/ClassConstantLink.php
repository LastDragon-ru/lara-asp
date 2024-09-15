<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

readonly class ClassConstantLink implements Link {
    public function __construct(
        public string $class,
        public string $constant,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->constant}";
    }
}
