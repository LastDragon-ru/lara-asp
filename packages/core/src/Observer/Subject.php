<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use Closure;

/**
 * @template TContext
 */
interface Subject {
    /**
     * @param Closure(TContext):void $observer
     */
    public function attach(Closure $observer): void;

    /**
     * @param Closure(TContext):void $observer
     */
    public function detach(Closure $observer): void;
}
