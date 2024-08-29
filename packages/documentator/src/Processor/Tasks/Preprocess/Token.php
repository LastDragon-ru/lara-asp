<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;

/**
 * @internal
 *
 * @template TParameters of Parameters
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
         * @var non-empty-list<Block>
         */
        public array $nodes,
    ) {
        // empty
    }
}
