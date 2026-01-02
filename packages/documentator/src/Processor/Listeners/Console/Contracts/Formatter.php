<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Status;

interface Formatter {
    /**
     * @param class-string<Task> $task
     */
    public function task(string $task): string;

    public function hook(Hook $hook): string;

    /**
     * Single character expected.
     */
    public function mark(Mark $mark): string;

    /**
     * Single character expected.
     */
    public function flag(Flag $flag): string;

    public function status(Status $status): string;

    public function message(Message $message): string;

    public function integer(int $value): string;

    public function filesize(int $value): string;

    public function duration(float $value): string;
}
