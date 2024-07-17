<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Parameters;

/**
 * @internal
 *
 * @template TParameters of Parameters|null
 */
class Token {
    public function __construct(
        /**
         * @var Instruction<TParameters>
         */
        public readonly Instruction $instruction,
        public readonly Context $context,
        public readonly string $target,
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
