<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Reference;

use Override;

class ClassPropertyReference extends Reference {
    public function __construct(
        public readonly string $class,
        public readonly string $property,
    ) {
        parent::__construct();
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::\${$this->property}";
    }
}
