<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

/**
 * @template TInstance of object
 */
readonly abstract class InstanceConfiguration {
    public function __construct() {
        // empty
    }

    /**
     * @param TInstance $task
     */
    abstract public function __invoke(object $task): void;
}
