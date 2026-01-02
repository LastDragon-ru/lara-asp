<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults;

use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Contracts\Formatter as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Status;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use Override;

use function number_format;

class Formatter implements Contract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function task(string $task): string {
        return $task;
    }

    #[Override]
    public function hook(Hook $hook): string {
        return match ($hook) {
            Hook::Before => 'before',
            Hook::After  => 'after',
            Hook::File   => 'file',
        };
    }

    #[Override]
    public function mark(Mark $mark): string {
        return match ($mark) {
            Mark::Done     => '✓',
            Mark::Fail     => '✕',
            Mark::Dot      => '·',
            Mark::Info     => '?',
            Mark::Task     => '●',
            Mark::Hook     => '@',
            Mark::Fill     => '.',
            Mark::Inout    => '↔',
            Mark::Input    => '→',
            Mark::Output   => '←',
            Mark::External => '!',
        };
    }

    #[Override]
    public function flag(Flag $flag): string {
        return match ($flag) {
            Flag::Read   => 'R',
            Flag::Write  => 'W',
            Flag::Delete => 'D',
        };
    }

    #[Override]
    public function status(Status $status): string {
        return match ($status) {
            Status::Use  => 'USE',
            Status::Done => 'DONE',
            Status::Null => 'NULL',
            Status::Skip => 'SKIP',
            Status::Next => 'NEXT',
            Status::Save => 'SAVE',
            Status::Fail => 'FAIL',
        };
    }

    #[Override]
    public function message(Message $message): string {
        return match ($message) {
            Message::Title     => 'Processing',
            Message::Failed    => 'Failed',
            Message::Completed => 'Completed',
            Message::Self      => 'Self',
            Message::Files     => 'Files',
            Message::Memory    => 'Memory',
            Message::Read      => 'Reads',
            Message::Write     => 'Writes',
            Message::Delete    => 'Deletes',
            Message::Inout     => 'Input/Output',
            Message::Input     => 'Input',
            Message::Output    => 'Output',
            Message::Include   => 'Include',
            Message::Exclude   => 'Exclude',
        };
    }

    #[Override]
    public function integer(int $value): string {
        return number_format($value);
    }

    #[Override]
    public function filesize(int $value): string {
        return match (true) {
            $value < 1024 * 1024 => number_format($value / 1024, 2).' KiB',
            default              => number_format($value / 1024 / 1024, 2).' MiB',
        };
    }

    #[Override]
    public function duration(float $value): string {
        return number_format($value, 3).' s';
    }
}
