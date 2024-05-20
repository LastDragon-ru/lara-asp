<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;

/**
 * @internal
 *
 * @template TTarget
 * @template TParameters of object|null
 */
class Token {
    public function __construct(
        /**
         * @var Instruction<TTarget, TParameters>
         */
        public readonly Instruction $instance,
        public readonly Context $context,
        /**
         * @var TTarget
         */
        public readonly mixed $target,
        /**
         * @var TParameters
         */
        public readonly mixed $parameters,
        /**
         * @var array<string, string>
         */
        public array $matches,
    ) {
        // empty
    }
}
