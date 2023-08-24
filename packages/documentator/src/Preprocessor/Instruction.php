<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

interface Instruction {
    public static function getName(): string;

    public static function getDescription(): string;

    public static function getTargetDescription(): ?string;

    public function process(string $path, string $target): string;
}
