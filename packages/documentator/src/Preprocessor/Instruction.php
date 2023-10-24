<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;

interface Instruction extends ProcessableInstruction {
    public static function getName(): string;

    public static function getDescription(): string;

    public static function getTargetDescription(): ?string;

    public function process(string $path, string $target): string;
}
