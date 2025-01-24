<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use UnexpectedValueException;

use function array_filter;
use function array_intersect_key;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_sum;
use function array_unique;
use function array_values;
use function count;
use function end;
use function implode;
use function ksort;
use function mb_strlen;
use function mb_substr;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function min;
use function str_repeat;
use function strip_tags;

use const SORT_REGULAR;

class Writer {
    /**
     * @var list<File|Task>
     */
    private array $stack = [];

    /**
     * @var list<Change>
     */
    private array $changes = [];

    private int   $width          = 150;
    private float $start          = 0;
    private int   $startMemory    = 0;
    private int   $peakMemory     = 0;
    private int   $filesCreated   = 0;
    private int   $filesUpdated   = 0;
    private int   $filesProcessed = 0;

    public function __construct(
        protected readonly OutputInterface $output,
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
        } elseif ($event instanceof FileFinished) {
            $this->fileFinished($event);
        } elseif ($event instanceof TaskStarted) {
            $this->taskStarted($event);
        } elseif ($event instanceof TaskFinished) {
            $this->taskFinished($event);
        } elseif ($event instanceof DependencyResolved) {
            $this->dependency($event);
        } elseif ($event instanceof FileSystemModified) {
            $this->filesystem($event);
        } else {
            // empty
        }
    }

    protected function processingStarted(ProcessingStarted $event): void {
        $this->width          = $this->getTerminalWidth();
        $this->stack          = [];
        $this->changes        = [];
        $this->start          = microtime(true);
        $this->peakMemory     = memory_get_peak_usage(true);
        $this->startMemory    = memory_get_usage(true);
        $this->filesCreated   = 0;
        $this->filesUpdated   = 0;
        $this->filesProcessed = 0;
    }

    protected function processingFinished(ProcessingFinished $event): void {
        // Write
        $time    = microtime(true) - $this->start;
        $peak    = memory_get_peak_usage(true);
        $memory  = $peak > $this->peakMemory ? $peak - $this->startMemory : 0;
        $message = $this->message('✓', ProcessingFinishedResult::Success)
            ." Files: {$this->formatter->integer($this->filesProcessed)}"
            .", [{$this->message('U', FileSystemModifiedType::Updated)}]pdated: {$this->message($this->formatter->integer($this->filesUpdated), FileSystemModifiedType::Updated)}"
            .", [{$this->message('C', FileSystemModifiedType::Created)}]reated: {$this->message($this->formatter->integer($this->filesCreated), FileSystemModifiedType::Created)}"
            .", Memory: {$this->formatter->filesize($memory)}";

        $this->line(0, $message, $time, $event->result, []);

        // Reset
        // (should we throw error if any of the stacks are not empty?)
        $this->width          = 0;
        $this->start          = 0;
        $this->stack          = [];
        $this->changes        = [];
        $this->peakMemory     = 0;
        $this->startMemory    = 0;
        $this->filesCreated   = 0;
        $this->filesUpdated   = 0;
        $this->filesProcessed = 0;
    }

    protected function fileStarted(FileStarted $event): void {
        $this->stack[] = new File($event->path, microtime(true), changes: $this->changes($event->path));

        $this->filesProcessed++;
    }

    protected function fileFinished(FileFinished $event): void {
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
        $this->line(0, $file->path, $time, $event->result, $file->changes);

        $this->children(1, $file->children);

        // Stats
        $created = false;
        $updated = false;

        foreach ($file->changes as $change) {
            if ($file->path !== $change->path) {
                continue;
            }

            if ($change->type === FileSystemModifiedType::Created) {
                $created = true;
            } else {
                $updated = true;
            }

            if ($created && $updated) {
                break;
            }
        }

        if ($created) {
            $this->filesCreated++;
        } elseif ($updated) {
            $this->filesUpdated++;
        } else {
            // skip
        }

        // Clear
        $this->changes = [];
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
        $file->changes    = array_values(array_unique(array_merge($file->changes, $task->changes), SORT_REGULAR));
        $file->children[] = new Item(
            $task->title,
            microtime(true) - $task->start - $task->paused,
            $event->result,
            $task->changes,
            $task->children,
        );

        // Clear
        $this->changes = [];
    }

    protected function dependency(DependencyResolved $event): void {
        // Task?
        $task = end($this->stack);

        if (!($task instanceof Task)) {
            throw new UnexpectedValueException('The top item in the stack is not a task.');
        }

        // Save
        $task->children[] = new Item($event->path, null, $event->result, $this->changes);
        $task->changes    = array_values(array_unique(array_merge($task->changes, $this->changes), SORT_REGULAR));

        // Clear
        $this->changes = [];
    }

    protected function filesystem(FileSystemModified $event): void {
        $this->changes[] = new Change($event->path, $event->type);
    }

    /**
     * @param list<Change> $changes
     */
    protected function line(
        int $level,
        string $path,
        ?float $time,
        ProcessingFinishedResult|FileFinishedResult|TaskFinishedResult|DependencyResolvedResult $result,
        array $changes,
    ): void {
        if ((!$this->isLevelVisible($level) || !$this->isResultVisible($result))) {
            return;
        }

        $flag     = null;
        $flags    = $this->flags($changes, $path, $flag);
        $template = [
            'prefix'   => mb_substr(str_repeat('<fg=gray>·</> ', $level), 0, -1),
            'message'  => $this->message($path, $flag),
            'spacer'   => '',
            'duration' => $time !== null ? "<fg=gray>{$this->formatter->duration($time)}</>" : '',
            'flags'    => str_repeat('<fg=gray>.</>', 3 - count($flags)).implode('', $flags),
            'suffix'   => $this->message($this->result($result), $result),
        ];
        $length   = array_map($this->length(...), $template);
        $filled   = array_filter($length, static fn ($value) => $value > 0);
        $spacer   = $this->width - array_sum($length) - count($filled) - 2;

        if ($spacer > 0) {
            $template['spacer'] = '<fg=gray>'.str_repeat('.', $spacer).'</>';
            $filled['spacer']   = '';
        }

        $template = array_intersect_key($template, $filled);
        $template = implode(' ', $template);

        $this->output->writeln($template);
    }

    /**
     * @param list<Change> $changes
     *
     * @return list<string>
     */
    protected function flags(array $changes, string $path, FileSystemModifiedType|Flag|null &$flag): array {
        // Collect
        $flag  = null;
        $flags = [];

        foreach ($changes as $change) {
            // Type
            $result         = $this->message($this->result($change->type), $change->type);
            $flags[$result] = ($flags[$result] ?? 0) + 1;

            // Path
            if ($path === $change->path) {
                $flag = $flag === null || $flag === $change->type ? $change->type : Flag::Mixed;
            }
        }

        // Sort
        ksort($flags);

        // Return
        return array_keys($flags);
    }

    protected function length(string $message): int {
        return mb_strlen(strip_tags($message));
    }

    protected function message(
        string $message,
        ProcessingFinishedResult|FileFinishedResult|TaskFinishedResult|DependencyResolvedResult|FileSystemModifiedType|Flag|null $status = null,
    ): string {
        $style   = $this->style($status);
        $message = $style !== null ? "<{$style}>{$message}</>" : $message;

        return $message;
    }

    protected function style(
        ProcessingFinishedResult|FileFinishedResult|TaskFinishedResult|DependencyResolvedResult|FileSystemModifiedType|Flag|null $result,
    ): ?string {
        return match ($result) {
            ProcessingFinishedResult::Success => 'fg=green;options=bold',
            ProcessingFinishedResult::Failed  => 'fg=red;options=bold',
            FileFinishedResult::Success       => 'fg=green',
            FileFinishedResult::Failed        => 'fg=red',
            FileFinishedResult::Skipped       => 'fg=gray',
            TaskFinishedResult::Success       => 'fg=green',
            TaskFinishedResult::Failed        => 'fg=red',
            DependencyResolvedResult::Success => 'fg=green',
            DependencyResolvedResult::Failed  => 'fg=red',
            DependencyResolvedResult::Missed  => 'fg=yellow',
            DependencyResolvedResult::Null    => 'fg=gray',
            FileSystemModifiedType::Created   => 'fg=green',
            FileSystemModifiedType::Updated   => 'fg=yellow',
            Flag::Mixed                       => 'options=underscore',
            null                              => null,
        };
    }

    protected function result(
        ProcessingFinishedResult|FileFinishedResult|TaskFinishedResult|DependencyResolvedResult|FileSystemModifiedType $result,
    ): string {
        return match ($result) {
            ProcessingFinishedResult::Success => 'DONE',
            ProcessingFinishedResult::Failed  => 'FAIL',
            FileFinishedResult::Success       => 'DONE',
            FileFinishedResult::Failed        => 'FAIL',
            FileFinishedResult::Skipped       => 'SKIP',
            TaskFinishedResult::Success       => 'DONE',
            TaskFinishedResult::Failed        => 'FAIL',
            DependencyResolvedResult::Success => 'DONE',
            DependencyResolvedResult::Failed  => 'FAIL',
            DependencyResolvedResult::Missed  => 'MISS',
            DependencyResolvedResult::Null    => 'NULL',
            FileSystemModifiedType::Created   => 'C',
            FileSystemModifiedType::Updated   => 'U',
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
        ProcessingFinishedResult|FileFinishedResult|TaskFinishedResult|DependencyResolvedResult $result,
    ): bool {
        return match ($result) {
            FileFinishedResult::Skipped => $this->output->isDebug(),
            default                     => true,
        };
    }

    protected function getTerminalWidth(): int {
        return min((new Terminal())->getWidth(), 150);
    }

    /**
     * @param list<Item> $children
     */
    private function children(int $level, array $children): void {
        if (!$this->isLevelVisible($level)) {
            return;
        }

        foreach ($children as $child) {
            $this->line($level, $child->title, $child->time, $child->result, $child->changes);
            $this->children($level + 1, $child->children);
        }
    }

    /**
     * @return list<Change>
     */
    private function changes(string $path): array {
        $changes = [];

        foreach ($this->stack as $item) {
            foreach ($item->changes as $change) {
                if ($change->path === $path) {
                    $changes[] = $change;
                }
            }
        }

        return array_values(array_unique($changes, SORT_REGULAR));
    }
}
