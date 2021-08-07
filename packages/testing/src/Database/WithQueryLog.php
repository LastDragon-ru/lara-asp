<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

/**
 * Enable QueryLog (the {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits} is required).
 *
 * @deprecated Please use {@link \LastDragon_ru\LaraASP\Testing\Database\QueryLog} instead.
 *
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @property-read Application $app
 *
 * @mixin TestCase
 */
trait WithQueryLog {
    private bool $withQueryLogEnabled = false;

    public function setUpWithQueryLog(): void {
        $db                        = $this->app->make('db');
        $this->withQueryLogEnabled = !$db->logging();

        if ($this->withQueryLogEnabled) {
            $db->enableQueryLog();
        }
    }

    public function tearDownWithQueryLog(): void {
        if ($this->withQueryLogEnabled) {
            $db                        = $this->app->make('db');
            $this->withQueryLogEnabled = false;

            $db->disableQueryLog();
            $db->flushQueryLog();
        }
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
