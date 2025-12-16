<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use UnitEnum;

/**
 * @internal
 */
class Dispatcher {
    /**
     * @var Closure(Event): void
     */
    private Closure $listener;

    /**
     * @param Closure(Event): void|null $listener
     */
    public function __construct(?Closure $listener) {
        $this->listener = $listener ?? static function (Event $event): void {
            // empty
        };
    }

    /**
     * @template T of ?UnitEnum
     *
     * @param T $result
     *
     * @return T
     */
    public function __invoke(Event $event, ?UnitEnum $result = null): ?UnitEnum {
        ($this->listener)($event);

        return $result;
    }
}
