<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Output;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Changes\ReadBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Changes\WriteBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\DependencyBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\ProcessBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\SourceBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Sources\FileBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Sources\HookBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\TaskBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Memory;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use UnexpectedValueException;

use function array_first;
use function array_last;
use function array_pop;
use function microtime;
use function sprintf;

class Logger {
    /**
     * @var list<Block>
     */
    private array    $stack = [];
    private Renderer $renderer;

    public function __construct(
        protected readonly Output $output,
        protected readonly Formatter $formatter,
    ) {
        $this->renderer = new Renderer($this->output, $this->formatter);
    }

    public function __invoke(Event $event): void {
        // Create
        $time  = $this->time();
        $block = null;

        if ($event instanceof ProcessBegin) {
            $block         = new ProcessBlock(
                $time,
                $this->memory(),
                $event->input,
                $event->output,
                $event->include,
                $event->exclude,
            );
            $this->stack[] = $block;
        } elseif ($event instanceof ProcessEnd) {
            $block = $this->pop(ProcessBlock::class)->end(
                match ($event->result) {
                    ProcessResult::Success => Status::Done,
                    ProcessResult::Error   => Status::Fail,
                },
                $time,
                $this->memory(),
            );
        } elseif ($event instanceof HookBegin) {
            $this->stack[] = new HookBlock($time, $event->hook, ...$this->path($event->path));
        } elseif ($event instanceof HookEnd) {
            $block = $this->pop(SourceBlock::class)->end(
                match ($event->result) {
                    HookResult::Success => Status::Done,
                    HookResult::Error   => Status::Fail,
                },
                $time,
            );
        } elseif ($event instanceof FileBegin) {
            $this->stack[] = new FileBlock($time, ...$this->path($event->path));
        } elseif ($event instanceof FileEnd) {
            $block = $this->pop(SourceBlock::class)->end(
                match ($event->result) {
                    FileResult::Success => Status::Done,
                    FileResult::Skipped => Status::Skip,
                    FileResult::Error   => Status::Fail,
                },
                $time,
            );
        } elseif ($event instanceof TaskBegin) {
            $this->stack[] = new TaskBlock($time, $event->task);
        } elseif ($event instanceof TaskEnd) {
            $block = $this->pop(TaskBlock::class)->end(
                match ($event->result) {
                    TaskResult::Success => Status::Done,
                    TaskResult::Error   => Status::Fail,
                },
                $time,
            );
        } elseif ($event instanceof Dependency) {
            $block = new DependencyBlock($time, ...$this->path($event->path));
            $block = $block->end(
                match ($event->result) {
                    DependencyResult::Found    => Status::Use,
                    DependencyResult::NotFound => Status::Null,
                    DependencyResult::Queued   => Status::Next,
                    DependencyResult::Saved    => Status::Save,
                },
                $time,
            );
        } elseif ($event instanceof FileSystemReadBegin) {
            $this->stack[] = new ReadBlock($time, ...$this->path($event->path));
        } elseif ($event instanceof FileSystemReadEnd) {
            $block = $this->pop(ReadBlock::class)->end(
                match ($event->result) {
                    FileSystemReadResult::Success => Status::Done,
                    FileSystemReadResult::Error   => Status::Fail,
                },
                $time,
                $event->bytes,
            );
        } elseif ($event instanceof FileSystemWriteBegin) {
            $this->stack[] = new WriteBlock($time, ...$this->path($event->path));
        } elseif ($event instanceof FileSystemWriteEnd) {
            $block = $this->pop(WriteBlock::class)->end(
                match ($event->result) {
                    FileSystemWriteResult::Success => Status::Done,
                    FileSystemWriteResult::Error   => Status::Fail,
                },
                $time,
                $event->bytes,
            );
        } else {
            // empty
        }

        // Add
        if ($block !== null && !($block instanceof ProcessBlock)) {
            $parent = $this->current();

            if ($parent->child($block)) {
                $block = $parent->add($block);
            } else {
                $block = $parent->subtract($block);
                $block = $this->root()->add($block);
            }
        }

        // Render
        if ($block !== null) {
            $this->write($block);
        }
    }

    private function write(Block $block): void {
        $rendered = $block->render($this->renderer, $this->formatter, 0);

        foreach ($rendered as $verbosity => $lines) {
            foreach ($lines as $line) {
                $this->output->write($line, $verbosity);
            }
        }
    }

    private function root(): ProcessBlock {
        $block = array_first($this->stack);

        if (!($block instanceof ProcessBlock)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Expected root block to be an instance of `%s`, got `%s`.',
                    ProcessBlock::class,
                    $block !== null ? $block::class : 'null',
                ),
            );
        }

        return $block;
    }

    private function current(): Block {
        $block = array_last($this->stack);

        if ($block === null) {
            throw new UnexpectedValueException('Expected block, got `null`.');
        }

        return $block;
    }

    /**
     * @template T of Block
     *
     * @param class-string<T> $expected
     *
     * @return T
     */
    private function pop(string $expected): Block {
        $block = array_pop($this->stack);

        if (!($block instanceof $expected)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Expected block to be an instance of `%s`, got `%s`.',
                    $expected,
                    $block !== null ? $block::class : 'null',
                ),
            );
        }

        return $block;
    }

    /**
     * @private `protected` is used for tests
     *
     * @return array{Mark, string}
     */
    protected function path(DirectoryPath|FilePath $path): array {
        $block = $this->root();
        $path  = $block->input->resolve($path);
        $name  = match (true) {
            $block->input->equals($block->output) && $block->input->contains($path),
                => [Mark::Inout, $block->output->relative($path)->path ?? $path->path],
            $block->output->contains($path),
            $block->output->equals($path),
                => [Mark::Output, $block->output->relative($path)->path ?? $path->path],
            $block->input->contains($path),
            $block->input->equals($path),
                => [Mark::Input, $block->input->relative($path)->path ?? $path->path],
            default
                => [Mark::External, $path->path],
        };

        return $name;
    }

    /**
     * @private `protected` is used for tests
     */
    protected function time(): float {
        return microtime(true);
    }

    /**
     * @private `protected` is used for tests
     */
    protected function memory(): Memory {
        return new Memory();
    }
}
