<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

class Context {
    public function __construct(
        public readonly string $path,
        public readonly string $target,
        public readonly ?string $parameters,
    ) {
        // empty
    }
}
