<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Events\Dispatcher;
use Override;

// TODO [laravel] [update] \Illuminate\Events\Dispatcher

/**
 * Fixed error dispatcher.
 *
 * It must be added to `bootstrap/app.php` by hand:
 *
 *      $app->singleton('events', \LastDragon_ru\LaraASP\Queue\EventsDispatcher::class);
 *
 * @see https://github.com/laravel/framework/issues/25272
 */
class EventsDispatcher extends Dispatcher {
    public function __construct(Container $container = null) {
        parent::__construct($container);

        $this->setQueueResolver(function () {
            return $this->container->make(Factory::class);
        });
    }

    /**
     * Create the listener and job for a queued listener.
     *
     * Laravel use {@link \ReflectionClass::newInstanceWithoutConstructor()} that
     * make impossible create configurable event listeners so we need to fix it.
     *
     * @see          https://github.com/laravel/framework/issues/25272
     *
     * @inheritDoc
     *
     * @param array<array-key, mixed> $arguments
     *
     * @return array<array-key, mixed>
     */
    #[Override]
    protected function createListenerAndJob($class, $method, $arguments): array {
        $listener = $this->container->make($class);
        $options  = $this->propagateListenerOptions(
            $listener,
            new CallQueuedListener($class, $method, $arguments),
        );

        return [$listener, $options];
    }
}
