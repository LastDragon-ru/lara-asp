<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application;

use Illuminate\Console\Scheduling\Event;

/**
 * @internal
 */
interface ScheduleMatcher {
    public function isMatch(Event $event, mixed $task): bool;
}
