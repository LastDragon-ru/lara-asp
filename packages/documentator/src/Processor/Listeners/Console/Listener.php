<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use UnexpectedValueException;

use function array_filter;
use function array_intersect_key;
use function array_key_last;
use function array_last;
use function array_map;
use function array_merge;
use function array_pop;
use function array_sum;
use function array_unique;
use function array_values;
use function count;
use function end;
use function implode;
use function mb_strlen;
use function mb_substr;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function min;
use function str_repeat;
use function strip_tags;

use const SORT_REGULAR;

class Listener {
    /**
     * @var list<File|Task>
     */
    private array $stack = [];

    /**
     * @var list<Change>
     */
    private array $changes = [];

    private ?DirectoryPath $input          = null;
    private ?DirectoryPath $output         = null;
    private int            $width          = 150;
    private float          $start          = 0;
    private int            $startMemory    = 0;
    private int            $peakMemory     = 0;
    private int            $filesProcessed = 0;

    public function __construct(
        protected readonly OutputInterface $writer,
        protected readonly Formatter $formatter,
    ) {
        // empty
    }

    public function __invoke(Event $event): void {
        if ($event instanceof ProcessBegin) {
            $this->processingStarted($event);
        } elseif ($event instanceof ProcessEnd) {
            $this->processingFinished($event);
        } elseif ($event instanceof FileBegin) {
            $this->fileStarted($event);
        } elseif ($event instanceof FileEnd) {
            $this->fileFinished($event);
        } elseif ($event instanceof TaskBegin) {
            $this->taskStarted($event);
        } elseif ($event instanceof TaskEnd) {
            $this->taskFinished($event);
        } elseif ($event instanceof DependencyBegin || $event instanceof DependencyEnd) {
            $this->dependency($event);
        } else {
            // empty
        }
    }

    protected function processingStarted(ProcessBegin $event): void {
        $this->input          = $event->input;
        $this->output         = $event->output;
        $this->width          = $this->getTerminalWidth();
        $this->stack          = [];
        $this->changes        = [];
        $this->start          = microtime(true);
        $this->peakMemory     = memory_get_peak_usage(true);
        $this->startMemory    = memory_get_usage(true);
        $this->filesProcessed = 0;
    }

    protected function processingFinished(ProcessEnd $event): void {
        // Write
        $time    = microtime(true) - $this->start;
        $peak    = memory_get_peak_usage(true);
        $memory  = $peak > $this->peakMemory ? $peak - $this->startMemory : 0;
        $message = $this->message('✓', ProcessResult::Success)
            ." Files: {$this->formatter->integer($this->filesProcessed)}"
            .", Memory: {$this->formatter->filesize($memory)}";

        $this->line(0, $message, $time, $event->result, []);

        // Reset
        // (should we throw error if any of the stacks are not empty?)
        $this->input          = null;
        $this->output         = null;
        $this->width          = 0;
        $this->start          = 0;
        $this->stack          = [];
        $this->changes        = [];
        $this->peakMemory     = 0;
        $this->startMemory    = 0;
        $this->filesProcessed = 0;
    }

    protected function fileStarted(FileBegin $event): void {
        $pathname      = $this->pathname($event->path);
        $this->stack[] = new File($pathname, microtime(true), changes: $this->changes($pathname));

        $this->filesProcessed++;
    }

    protected function fileFinished(FileEnd $event): void {
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

        // Clear
        $this->changes = [];
    }

    protected function taskStarted(TaskBegin $event): void {
        $this->stack[] = new Task($event->task, microtime(true));
    }

    protected function taskFinished(TaskEnd $event): void {
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

    protected function dependency(DependencyBegin|DependencyEnd $event): void {
        // Task?
        $task = end($this->stack);

        if (!($task instanceof Task)) {
            throw new UnexpectedValueException('The top item in the stack is not a task.');
        }

        // Save
        if ($event instanceof DependencyBegin) {
            $task->children[] = new Item($this->pathname($event->path), null, DependencyResult::Resolved, $this->changes);
            $task->changes    = array_values(array_unique(array_merge($task->changes, $this->changes), SORT_REGULAR));
            $this->changes    = [];
        } else {
            $item = array_last($task->children);

            if ($item !== null) {
                $item->result = $event->result;
            }
        }
    }

    /**
     * @param list<Change> $changes
     */
    protected function line(
        int $level,
        string $path,
        ?float $time,
        ProcessResult|FileResult|TaskResult|DependencyResult $result,
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

        $this->writer->writeln($template);
    }

    /**
     * @param list<Change> $changes
     *
     * @return list<string>
     */
    protected function flags(array $changes, string $path, ?Flag &$flag): array {
        return [];
    }

    protected function length(string $message): int {
        return mb_strlen(strip_tags($message));
    }

    protected function message(
        string $message,
        ProcessResult|FileResult|TaskResult|DependencyResult|Flag|null $status = null,
    ): string {
        $style   = $this->style($status);
        $message = $style !== null ? "<{$style}>{$message}</>" : $message;

        return $message;
    }

    protected function style(
        ProcessResult|FileResult|TaskResult|DependencyResult|Flag|null $result,
    ): ?string {
        return match ($result) {
            ProcessResult::Success     => 'fg=green;options=bold',
            ProcessResult::Error       => 'fg=red;options=bold',
            FileResult::Success        => 'fg=green',
            FileResult::Error          => 'fg=red',
            FileResult::Skipped        => 'fg=gray',
            TaskResult::Success        => 'fg=green',
            TaskResult::Error          => 'fg=red',
            DependencyResult::Resolved => 'fg=green',
            DependencyResult::Error    => 'fg=red',
            DependencyResult::NotFound => 'fg=gray',
            DependencyResult::Queued   => null,
            Flag::Mixed                => 'options=underscore',
            null                       => null,
        };
    }

    protected function result(
        ProcessResult|FileResult|TaskResult|DependencyResult $result,
    ): string {
        return match ($result) {
            ProcessResult::Success     => 'DONE',
            ProcessResult::Error       => 'FAIL',
            FileResult::Success        => 'DONE',
            FileResult::Error          => 'FAIL',
            FileResult::Skipped        => 'SKIP',
            TaskResult::Success        => 'DONE',
            TaskResult::Error          => 'FAIL',
            DependencyResult::Resolved => 'DONE',
            DependencyResult::Error    => 'FAIL',
            DependencyResult::NotFound => 'NULL',
            DependencyResult::Queued   => 'NEXT',
        };
    }

    protected function isLevelVisible(int $level): bool {
        return match ($level) {
            0       => true,
            1       => $this->writer->isVerbose(),
            2       => $this->writer->isVeryVerbose(),
            3       => $this->writer->isDebug(),
            default => false,
        };
    }

    protected function isResultVisible(
        ProcessResult|FileResult|TaskResult|DependencyResult $result,
    ): bool {
        return match ($result) {
            FileResult::Skipped => $this->writer->isDebug(),
            default             => true,
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

    /**
     * @return non-empty-string
     */
    protected function pathname(DirectoryPath|FilePath $path): string {
        if ($this->input === null || $this->output === null) {
            return Mark::Unknown->value.' '.$path;
        }

        $path = $this->input->resolve($path);
        $name = match (true) {
            $this->input->equals($this->output) && $this->input->contains($path),
                => Mark::Inout->value.' '.$this->output->relative($path),
            $this->output->contains($path),
            $this->output->equals($path),
                => Mark::Output->value.' '.$this->output->relative($path),
            $this->input->contains($path),
            $this->input->equals($path),
                => Mark::Input->value.' '.$this->input->relative($path),
            default
                => Mark::External->value.' '.$path,
        };

        return $name;
    }
}
