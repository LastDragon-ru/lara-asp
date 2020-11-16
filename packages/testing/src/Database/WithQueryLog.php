<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

/**
 * Enable QueryLog (the {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits} is required).
 *
 * @mixin \Illuminate\Foundation\Testing\TestCase
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
}
