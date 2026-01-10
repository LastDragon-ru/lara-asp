<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Application;

use Closure;

/**
 * Laravel Octane does not recommend injecting container/config/etc because it
 * may lead to using stale versions of them. The {@see Resolver} and its
 * subclasses designed specially to fix the problem.
 *
 * @see https://laravel.com/docs/octane#dependency-injection-and-octane
 *
 * @template TInstance of object
 */
abstract class Resolver {
    /**
     * @param Closure(): TInstance $resolver
     */
    public function __construct(
        protected readonly Closure $resolver,
    ) {
        // empty
    }

    /**
     * @return TInstance
     */
    public function getInstance(): object {
        return ($this->resolver)();
    }
}
