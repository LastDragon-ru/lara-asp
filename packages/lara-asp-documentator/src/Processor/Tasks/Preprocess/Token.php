<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
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
        /**
         * @var TParameters
         */
        public readonly Parameters $parameters,
        /**
         * @var non-empty-list<Node>
         */
        public array $nodes,
    ) {
        // empty
    }
}
