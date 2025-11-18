<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use IteratorAggregate;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Casts;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Tasks;
use Override;
use Traversable;

use function array_unique;
use function array_values;
use function is_a;

/**
 * Perform one or more task on the file(s).
 */
class Processor {
    protected readonly Tasks      $tasks;
    protected readonly Casts      $casts;
    protected readonly Dispatcher $dispatcher;

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Adapter $adapter,
    ) {
        $this->tasks      = new Tasks($container);
        $this->casts      = new Casts($container);
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
     * The last added resolvers have a bigger priority.
     *
     * @template V of object
     * @template R of Cast<V>
     *
     * @param R|class-string<R> $cast
     */
    public function cast(Cast|string $cast, ?int $priority = null): static {
        $this->casts->add($cast, $priority);

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
    public function run(
        DirectoryPath|FilePath $input,
        ?DirectoryPath $output = null,
        array $skip = [],
    ): void {
        // Prepare
        $root = $input->getDirectoryPath('.');

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$root->isEqual($output) && $root->isInside($output)) {
                $skip[] = GlobMatcher::escape((string) $root->getRelativePath($output)).'**';
            }
        } else {
            $output = $root;
        }

        // Start
        try {
            $this->dispatcher->notify(new ProcessingStarted());

            try {
                $caster = new Caster($this->adapter, $this->casts);
                $fs     = new FileSystem($this->adapter, $this->dispatcher, $caster, $root, $output);
                $files  = match (true) {
                    default                    => $fs->getFilesIterator($root, $this->include(), $skip),
                    $input instanceof FilePath => new readonly class($fs, $input) implements IteratorAggregate {
                        public function __construct(
                            private FileSystem $fs,
                            private FilePath $path,
                        ) {
                            // empty
                        }

                        /**
                         * @return Traversable<int, File>
                         */
                        #[Override]
                        public function getIterator(): Traversable {
                            yield $this->fs->getFile($this->path);
                        }
                    },
                };

                $this->execute($fs, $files, new Glob($root, $skip));
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new ProcessingFailed($exception);
            }

            $this->dispatcher->notify(new ProcessingFinished(ProcessingFinishedResult::Success));
        } catch (Exception $exception) {
            $this->dispatcher->notify(new ProcessingFinished(ProcessingFinishedResult::Failed));

            throw $exception;
        }
    }

    /**
     * @param iterable<mixed, File> $files
     */
    protected function execute(FileSystem $fs, iterable $files, Matcher $skipped): void {
        $executor = new Executor($this->dispatcher, $this->tasks, $fs, $files, $skipped);

        $executor->run();
    }

    /**
     * @return list<string>
     */
    private function include(): array {
        $include = [];

        foreach ($this->tasks as $task) {
            if (is_a($task, FileTask::class, true)) {
                foreach ($task::getExtensions() as $extension) {
                    $extension = '**/*'.($extension !== '*' ? ".{$extension}" : '');
                    $include[] = $extension;
                }
            }
        }

        return array_values(array_unique($include));
    }
}
