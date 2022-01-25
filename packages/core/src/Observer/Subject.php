<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use Closure;
use SplObjectStorage;

use function array_values;
use function iterator_to_array;

/**
 * @template TContext
 */
class Subject {
    /**
     * @var SplObjectStorage<Closure(TContext):void>
     */
    private SplObjectStorage $observers;

    public function __construct() {
        $this->reset();
    }

    /**
     * @param Closure(TContext):void $observer
     */
    public function attach(Closure $observer): void {
        $this->observers->attach($observer);
    }

    /**
     * @param Closure(TContext):void $observer
     */
    public function detach(Closure $observer): void {
        $this->observers->detach($observer);
    }

    public function reset(): void {
        $this->observers = new SplObjectStorage();
    }

    /**
     * @param TContext $context
     */
    public function notify(mixed $context = null): void {
        foreach ($this->observers as $observer) {
            /** @var Closure $observer */
            $observer($context);
        }
    }

    /**
     * @return array<Closure(TContext):void>
     */
    public function getObservers(): array {
        return array_values(iterator_to_array($this->observers));
    }
}
