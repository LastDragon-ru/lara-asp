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
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
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
     * @param list<non-empty-string>        $skip Globs that shouldn't be processed.
     * @param Closure(Event): void|null     $onEvent
     * @param Closure(Exception): void|null $onError
     */
    public function __invoke(
        DirectoryPath|FilePath $input,
        ?DirectoryPath $output = null,
        array $skip = [],
        ?Closure $onEvent = null,
        ?Closure $onError = null,
    ): bool {
        // Prepare
        $root       = $input->directory();
        $dispatcher = new Dispatcher($onEvent);

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$root->equals($output) && $root->contains($output)) {
                $skip[] = GlobMatcher::escape((string) $root->relative($output)).'**';
            }
        } else {
            $output = $root;
        }

        // Start
        $globs  = array_map(static fn ($glob) => "**/{$glob}", $this->tasks->globs());
        $result = $dispatcher(new ProcessBegin($root, $output, $globs, $skip), ProcessResult::Success);

        try {
            $fs       = new FileSystem($this->adapter, $dispatcher, $root, $output);
            $files    = $input instanceof FilePath ? [$input] : $fs->search($root, $globs, $skip);
            $skipped  = new Glob($skip);
            $executor = new Executor($this->container, $dispatcher, $this->tasks, $fs, $files, $skipped);

            $executor->run();
        } catch (Exception $exception) {
            $result = ProcessResult::Error;

            if ($onError !== null) {
                $onError($exception);
            } else {
                throw $exception;
            }
        } finally {
            $dispatcher(new ProcessEnd($result));

            $this->reset();
        }

        // Return
        return $result === ProcessResult::Success;
    }

    protected function reset(): void {
        $this->tasks->reset();
    }
}
