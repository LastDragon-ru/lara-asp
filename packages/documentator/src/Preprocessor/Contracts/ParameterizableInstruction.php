<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @deprecated
 * @template TParameters of Serializable
 */
interface ParameterizableInstruction {
    /**
     * @return class-string<TParameters>
     */
    public static function getParameters(): string;

    /**
     * @param TParameters $parameters
     */
    public function process(string $path, string $target, Serializable $parameters): string;
}
