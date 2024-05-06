<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

/**
 * @deprecated
 */
interface ProcessableInstruction {
    public function process(string $path, string $target): string;
}
