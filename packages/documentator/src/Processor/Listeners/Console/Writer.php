<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use Illuminate\Console\OutputStyle;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileProcessed;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileProcessedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use Symfony\Component\Console\Terminal;
use UnexpectedValueException;

use function array_key_last;
use function array_pop;
use function end;
use function mb_strlen;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function min;
use function str_repeat;
use function strip_tags;
use function trim;

class Writer {
    /**
     * @var list<File|Task>
     */
    private array $stack       = [];
    private int   $width       = 150;
    private int   $files       = 0;
    private float $start       = 0;
    private int   $startMemory = 0;
    private int   $peakMemory  = 0;

    public function __construct(
        protected readonly OutputStyle $output,
        protected readonly Formatter $formatter,
    ) {
        // empty
    }

    public function __invoke(Event $event): void {
        if ($event instanceof ProcessingStarted) {
            $this->processingStarted($event);
        } elseif ($event instanceof ProcessingFinished) {
            $this->processingFinished($event);
        } elseif ($event instanceof FileStarted) {
            $this->fileStarted($event);
        } elseif ($event instanceof FileProcessed) {
            $this->fileFinished($event);
        } elseif ($event instanceof TaskStarted) {
            $this->taskStarted($event);
        } elseif ($event instanceof TaskFinished) {
            $this->taskFinished($event);
        } elseif ($event instanceof DependencyResolved) {
            $this->dependency($event);
        } else {
            // empty
        }
    }

    protected function processingStarted(ProcessingStarted $event): void {
        $this->width       = min((new Terminal())->getWidth(), 150);
        $this->files       = 0;
        $this->stack       = [];
        $this->start       = microtime(true);
        $this->peakMemory  = memory_get_peak_usage(true);
        $this->startMemory = memory_get_usage(true);
    }

    protected function processingFinished(ProcessingFinished $event): void {
        // Write
        $time    = microtime(true) - $this->start;
        $peak    = memory_get_peak_usage(true);
        $memory  = $peak > $this->peakMemory ? $peak - $this->startMemory : 0;
        $message = "Files: {$this->formatter->integer($this->files)}, Time: {$this->formatter->duration($time)}";

        if ($memory > 0) {
            $message .= ", Memory: {$this->formatter->filesize($memory)}";
        }

        $this->output->newLine();

        $this->line(0, $message, null, $event->result);

        $this->output->newLine();

        // Reset
        // (should we throw error if any of the stacks are not empty?)
        $this->width       = 0;
        $this->start       = 0;
        $this->stack       = [];
        $this->peakMemory  = 0;
        $this->startMemory = 0;
    }

    protected function fileStarted(FileStarted $event): void {
        $this->stack[] = new File($event->path, microtime(true));
        $this->files++;
    }

    protected function fileFinished(FileProcessed $event): void {
        // File?
        $file = array_pop($this->stack);

        if (!($file instanceof File)) {
            throw new UnexpectedValueException('The pop item in the stack is not a file.');
        }

        // Previous?
        $previous = array_key_last($this->stack);
        $time     = microtime(true) - $file->start - $file->paused;

        if ($previous !== null) {
            $this->stack[$previous]->paused += $time;
        }

        // Message
        $this->line(0, $file->title, $time, $event->result);

        $this->children(1, $file->children);
    }

    protected function taskStarted(TaskStarted $event): void {
        $this->stack[] = new Task($event->task, microtime(true));
    }

    protected function taskFinished(TaskFinished $event): void {
        // Task?
        $task = array_pop($this->stack);

        if (!($task instanceof Task)) {
            throw new UnexpectedValueException('The pop item in the stack is not a task.');
        }

        // File?
        $file = end($this->stack);

        if (!($file instanceof File)) {
            throw new UnexpectedValueException('The top item in the stack is not a file.');
        }

        // Save
        $file->paused    += $task->paused;
        $file->children[] = new Item(
            $task->title,
            microtime(true) - $task->start - $task->paused,
            $event->result,
            $task->children,
        );
    }

    protected function dependency(DependencyResolved $event): void {
        // Task?
        $task = end($this->stack);

        if (!($task instanceof Task)) {
            throw new UnexpectedValueException('The top item in the stack is not a task.');
        }

        // Save
        $task->children[] = new Item($event->path, null, $event->result);
    }

    /**
     * @param list<Item> $children
     */
    protected function children(int $level, array $children): void {
        if (!$this->isLevelVisible($level)) {
            return;
        }

        foreach ($children as $child) {
            $this->line($level, $child->title, $child->time, $child->result);
            $this->children($level + 1, $child->children);
        }
    }

    protected function line(
        int $level,
        string $message,
        ?float $time,
        ProcessingFinishedResult|FileProcessedResult|TaskFinishedResult|DependencyResolvedResult $result,
    ): void {
        if (!$this->isLevelVisible($level) || !$this->isResultVisible($result)) {
            return;
        }

        $prefix   = str_repeat('    ', $level);
        $suffix   = $this->result($result);
        $message  = trim($message);
        $duration = $this->formatter->duration($time);
        $spacer   = $this->width
            - $this->length($prefix)
            - $this->length($message)
            - $this->length($duration)
            - $this->length($suffix)
            - 5;
        $line     = $prefix
            .$message
            .' '.($spacer > 0 ? '<fg=gray>'.str_repeat('.', $spacer).'</>' : '')
            .' '."<fg=gray>{$duration}</>"
            .' '.$suffix;

        $this->output->writeln($line);
    }

    protected function length(string $message): int {
        return mb_strlen(strip_tags($message));
    }

    protected function result(
        ProcessingFinishedResult|FileProcessedResult|TaskFinishedResult|DependencyResolvedResult $enum,
    ): string {
        return match ($enum) {
            ProcessingFinishedResult::Success => '<fg=green;options=bold>DONE</>',
            ProcessingFinishedResult::Failed  => '<fg=red;options=bold>FAIL</>',
            FileProcessedResult::Success      => '<fg=green>DONE</>',
            FileProcessedResult::Failed       => '<fg=red>FAIL</>',
            FileProcessedResult::Skipped      => '<fg=gray>SKIP</>',
            TaskFinishedResult::Success       => '<fg=green>DONE</>',
            TaskFinishedResult::Failed        => '<fg=red>FAIL</>',
            DependencyResolvedResult::Success => '<fg=green>DONE</>',
            DependencyResolvedResult::Failed  => '<fg=red>FAIL</>',
            DependencyResolvedResult::Missed  => '<fg=yellow>MISS</>',
            DependencyResolvedResult::Null    => '<fg=gray>NULL</>',
        };
    }

    protected function isLevelVisible(int $level): bool {
        return match ($level) {
            0       => true,
            1       => $this->output->isVerbose(),
            2       => $this->output->isVeryVerbose(),
            3       => $this->output->isDebug(),
            default => false,
        };
    }

    protected function isResultVisible(
        ProcessingFinishedResult|FileProcessedResult|TaskFinishedResult|DependencyResolvedResult $enum,
    ): bool {
        return match ($enum) {
            FileProcessedResult::Skipped => $this->output->isDebug(),
            default                      => true,
        };
    }
}
