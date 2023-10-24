<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @template TParameters of Serializable
 */
interface ParameterizableInstruction extends Instruction {
    /**
     * @return class-string<TParameters>
     */
    public static function getParameters(): string;

    /**
     * @return non-empty-array<string, string>
     */
    public static function getParametersDescription(): array;

    /**
     * @param TParameters $parameters
     */
    public function process(string $path, string $target, Serializable $parameters): string;
}
