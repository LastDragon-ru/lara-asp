<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

interface ProcessableInstruction extends Instruction {
    public function process(string $path, string $target): string;
}
