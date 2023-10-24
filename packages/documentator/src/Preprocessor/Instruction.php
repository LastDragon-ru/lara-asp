<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;

/**
 * @deprecated 5.0.0 Please use {@see InstructionContract} and its subclasses instead.
 *
 * @see InstructionContract
 */
interface Instruction extends ProcessableInstruction {
    // empty
}
