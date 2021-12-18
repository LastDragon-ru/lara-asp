<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;

/**
 * Enable QueryLog.
 *
 * @deprecated Please use {@link \LastDragon_ru\LaraASP\Testing\Database\QueryLog} instead.
 *
 * @property-read Application $app
 *
 * @mixin TestCase
 */
trait WithQueryLog {
    private bool $withQueryLogEnabled = false;

    /**
     * @before
     * @internal
     */
    public function initWithQueryLog(): void {
        $this->afterApplicationCreated(function (): void {
            $db                        = $this->app->make('db');
            $this->withQueryLogEnabled = !$db->logging();

            if ($this->withQueryLogEnabled) {
                $db->enableQueryLog();
            }
        });

        $this->beforeApplicationDestroyed(function (): void {
            if ($this->withQueryLogEnabled) {
                $db                        = $this->app->make('db');
                $this->withQueryLogEnabled = false;

                $db->disableQueryLog();
                $db->flushQueryLog();
            }
        });
    }

    /**
     * @return array<array{query: string, bindings: array<mixed>, time: float|null}>
     */
    protected function getQueryLog(): array {
        return $this->app->make('db')->getQueryLog();
    }

    protected function flushQueryLog(): void {
        $this->app->make('db')->flushQueryLog();
    }
}
