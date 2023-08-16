<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use Closure;
use SplObjectStorage;
use function array_values;
use function iterator_to_array;

/**
 * @template TContext
 * @implements Subject<TContext>
 */
class Dispatcher implements Subject {
    /**
     * @var SplObjectStorage<Closure(TContext):void, null>
     */
    private SplObjectStorage $observers;

    public function __construct() {
        $this->reset();
    }

    public function attach(Closure $observer): static {
        $this->observers->attach($observer);

        return $this;
    }

    public function detach(Closure $observer): static {
        $this->observers->detach($observer);

        return $this;
    }

    public function reset(): static {
        $this->observers = new SplObjectStorage();

        return $this;
    }

    /**
     * @param TContext $context
     */
    public function notify(mixed $context = null): static {
        foreach ($this->observers as $observer) {
            $observer($context);
        }

        return $this;
    }

    /**
     * @return list<Closure(TContext):void>
     */
    public function getObservers(): array {
        return array_values(iterator_to_array($this->observers));
    }
}
