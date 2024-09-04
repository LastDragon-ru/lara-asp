<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Reference;

use Override;

class ClassConstantReference extends Reference {
    public function __construct(
        public readonly string $class,
        public readonly string $constant,
    ) {
        parent::__construct();
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->constant}";
    }
}
