<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
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
    protected readonly Tasks      $tasks;
    protected readonly Dispatcher $dispatcher;

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Adapter $adapter,
    ) {
        $this->tasks      = new Tasks($container);
        $this->dispatcher = new Dispatcher();
    }

    /**
     * The first added tasks have a bigger priority unless specify.
     *
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function task(Task|string $task, ?int $priority = null): static {
        $this->tasks->add($task, $priority);

        return $this;
    }

    /**
     * @param callable(Event): void $listener
     */
    public function listen(callable $listener): static {
        if (!($listener instanceof Closure)) {
            $listener = $listener(...);
        }

        $this->dispatcher->attach($listener);

        return $this;
    }

    /**
     * @param list<non-empty-string> $skip Globs that shouldn't be processed.
     */
    public function __invoke(
        DirectoryPath|FilePath $input,
        ?DirectoryPath $output = null,
        array $skip = [],
    ): void {
        // Prepare
        $root = $input->directory('.');

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$root->equals($output) && $root->contains($output)) {
                $skip[] = GlobMatcher::escape((string) $root->relative($output)).'**';
            }
        } else {
            $output = $root;
        }

        // Start
        try {
            $this->dispatcher->notify(new ProcessingStarted($root, $output));

            try {
                $fs    = new FileSystem($this->adapter, $this->dispatcher, $root, $output);
                $globs = array_map(static fn ($glob) => "**/{$glob}", $this->tasks->globs());
                $files = match (true) {
                    default                    => $fs->search($root, $globs, $skip),
                    $input instanceof FilePath => [$input],
                };

                $this->run($fs, $files, new Glob($skip));
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new ProcessingFailed($exception);
            }

            $this->dispatcher->notify(new ProcessingFinished(ProcessingFinishedResult::Success));
        } catch (Exception $exception) {
            $this->dispatcher->notify(new ProcessingFinished(ProcessingFinishedResult::Failed));

            throw $exception;
        } finally {
            $this->reset();
        }
    }

    /**
     * @param iterable<mixed, FilePath> $files
     */
    protected function run(FileSystem $fs, iterable $files, Matcher $skipped): void {
        $executor = new Executor($this->container, $this->dispatcher, $this->tasks, $fs, $files, $skipped);

        $executor->run();
    }

    protected function reset(): void {
        $this->adapter->reset();
        $this->tasks->reset();
    }
}
