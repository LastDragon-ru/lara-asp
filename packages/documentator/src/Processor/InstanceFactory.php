<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;

/**
 * @see https://github.com/phpstan/phpstan/issues/8770
 * @see https://github.com/phpstan/phpstan/issues/9521 (?)
 * @see https://github.com/phpstan/phpstan/discussions/11736
 *
 * @template TInstance of object
 */
readonly class InstanceFactory {
    public function __construct(
        /**
         * @var class-string<TInstance>
         */
        public string $class,
        /**
         * @var Closure(TInstance): void
         */
        public Closure $factory,
    ) {
        // empty
    }
}
