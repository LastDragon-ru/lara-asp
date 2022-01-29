<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use Closure;

/**
 * @template TContext
 */
interface Subject {
    /**
     * @param Closure(TContext):void $observer
     *
     * @return $this<TContext>
     */
    public function attach(Closure $observer): self;

    /**
     * @param Closure(TContext):void $observer
     *
     * @return $this<TContext>
     */
    public function detach(Closure $observer): self;

    /**
     * @return $this<TContext>
     */
    public function reset(): self;
}
