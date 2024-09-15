<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

readonly class ClassMethodLink implements Link {
    public function __construct(
        public string $class,
        public string $method,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->method}()";
    }
}
