<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;

/**
 * @template TParameters
 * @template TValue
 */
interface Resolver {
    /**
     * @param TParameters $parameters
     *
     * @return TValue
     */
    public function resolve(Context $context, mixed $parameters): mixed;
}
