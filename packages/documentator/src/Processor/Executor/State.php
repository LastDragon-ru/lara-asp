<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

/**
 * @internal
 */
enum State {
    case Preparation;
    case Iteration;
    case Finished;
    case Created;

    public function is(self ...$states): bool {
        foreach ($states as $state) {
            if ($state === $this) {
                return true;
            }
        }

        return false;
    }
}
