<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Laravel;

use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Formatter as DefaultFormatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Status;
use LastDragon_ru\LaraASP\Formatter\Formatter as FormatterImpl;
use Override;

class Formatter extends DefaultFormatter {
    public function __construct(
        protected readonly FormatterImpl $formatter,
    ) {
        parent::__construct();
    }

    #[Override]
    public function mark(Mark $mark): string {
        $string = parent::mark($mark);
        $string = match ($mark) {
            Mark::Done => "<fg=green;options=bold>{$string}</>",
            Mark::Fail => "<fg=red;options=bold>{$string}</>",
            Mark::Dot,
            Mark::Fill => "<fg=gray>{$string}</>",
            default    => $string,
        };

        return $string;
    }

    #[Override]
    public function flag(Flag $flag): string {
        $string = parent::flag($flag);
        $string = match ($flag) {
            Flag::Write  => "<fg=yellow>{$string}</>",
            Flag::Delete => "<fg=red>{$string}</>",
            default      => $string,
        };

        return $string;
    }

    #[Override]
    public function status(Status $status): string {
        $string = parent::status($status);
        $string = match ($status) {
            Status::Done => "<fg=green>{$string}</>",
            Status::Fail => "<fg=red>{$string}</>",
            Status::Null,
            Status::Skip => "<fg=gray>{$string}</>",
            default      => $string,
        };

        return $string;
    }

    #[Override]
    public function message(Message $message): string {
        $string = parent::message($message);
        $string = match ($message) {
            Message::Completed => "<fg=green;options=bold>{$string}</>",
            Message::Failed    => "<fg=red;options=bold>{$string}</>",
            default            => $string,
        };

        return $string;
    }

    #[Override]
    public function integer(int $value): string {
        return $this->formatter->integer($value);
    }

    #[Override]
    public function filesize(int $value): string {
        return $this->formatter->filesize($value);
    }

    #[Override]
    public function duration(float $value): string {
        return $this->formatter->duration($value);
    }
}
