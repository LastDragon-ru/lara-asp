<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

class ClassConstantLink extends Base implements Link {
    public function __construct(
        string $class,
        public readonly string $constant,
    ) {
        parent::__construct($class);
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->constant}";
    }
}
