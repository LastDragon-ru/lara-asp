<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatchers;

use Illuminate\Console\Scheduling\Event;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleMatcher;
use Override;

/**
 * @internal
 */
class DescriptionMatcher implements ScheduleMatcher {
    public function __construct() {
        // empty
    }

    #[Override]
    public function isMatch(Event $event, mixed $task): bool {
        return isset($event->description)
            && $event->description === $task;
    }
}
