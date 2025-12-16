<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Tasks;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

use function array_map;

/**
 * Perform one or more task on the file(s).
 */
class Processor {
    protected readonly Tasks $tasks;

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Adapter $adapter,
    ) {
        $this->tasks = new Tasks($container);
    }

    /**
     * The first added tasks have a bigger priority unless specify.
     *
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function task(Task|string $task, ?int $priority = null): void {
        $this->tasks->add($task, $priority);
    }

    /**
     * @param list<non-empty-string>    $skip Globs that shouldn't be processed.
     * @param Closure(Event): void|null $on
     */
    public function __invoke(
        DirectoryPath|FilePath $input,
        ?DirectoryPath $output = null,
        array $skip = [],
        ?Closure $on = null,
    ): void {
        // Prepare
        $root       = $input->directory();
        $dispatcher = new Dispatcher($on);

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$root->equals($output) && $root->contains($output)) {
                $skip[] = GlobMatcher::escape((string) $root->relative($output)).'**';
            }
        } else {
            $output = $root;
        }

        // Start
        $result = $dispatcher(new ProcessBegin($root, $output), ProcessResult::Success);

        try {
            try {
                $fs       = new FileSystem($this->adapter, $dispatcher, $root, $output);
                $globs    = array_map(static fn ($glob) => "**/{$glob}", $this->tasks->globs());
                $files    = $input instanceof FilePath ? [$input] : $fs->search($root, $globs, $skip);
                $skipped  = new Glob($skip);
                $executor = new Executor($this->container, $dispatcher, $this->tasks, $fs, $files, $skipped);

                $executor->run();
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new ProcessFailed($exception);
            }
        } catch (Exception $exception) {
            $result = ProcessResult::Error;

            throw $exception;
        } finally {
            $dispatcher(new ProcessEnd($result));

            $this->reset();
        }
    }

    protected function reset(): void {
        $this->adapter->reset();
        $this->tasks->reset();
    }
}
