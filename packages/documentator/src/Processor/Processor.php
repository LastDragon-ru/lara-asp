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
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Globs;
use Override;
use Traversable;

use function array_merge;
use function array_unique;
use function array_values;
use function is_a;

/**
 * Perform one or more task on the file(s).
 */
class Processor {
    private readonly Tasks        $tasks;
    private readonly Caster       $caster;
    protected readonly Dispatcher $dispatcher;

    /**
     * @var list<string>
     */
    private array $exclude = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly Adapter $adapter,
    ) {
        $this->tasks      = new Tasks($container);
        $this->caster     = new Caster($container, $this->adapter);
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @internal
     * @return iterable<array-key, class-string<Task>>
     */
    public function getTasks(): iterable {
        return $this->tasks;
    }

    /**
     * The first added tasks have a bigger priority unless specify.
     *
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function addTask(Task|string $task, ?int $priority = null): static {
        $this->tasks->add($task, $priority);

        return $this;
    }

    /**
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function removeTask(Task|string $task): static {
        $this->tasks->remove($task);

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
    public function addCast(Cast|string $cast, ?int $priority = null): static {
        $this->caster->addCast($cast, $priority);

        return $this;
    }

    /**
     * @template V of object
     * @template R of Cast<V>
     *
     * @param R|class-string<R> $cast
     */
    public function removeCast(Cast|string $cast): static {
        $this->caster->removeCast($cast);

        return $this;
    }

    /**
     * @param array<array-key, string>|string $exclude glob(s) to exclude.
     */
    public function exclude(array|string $exclude): static {
        $this->exclude = array_merge($this->exclude, array_values((array) $exclude));

        return $this;
    }

    /**
     * @param Closure(Event): void $listener
     */
    public function addListener(Closure $listener): static {
        $this->dispatcher->attach($listener);

        return $this;
    }

    public function run(DirectoryPath|FilePath $input, ?DirectoryPath $output = null): void {
        // Prepare
        $exclude   = $this->exclude;
        $directory = $input->getDirectoryPath('.');

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$directory->isEqual($output) && $directory->isInside($output)) {
                $exclude[] = GlobMatcher::escape((string) $directory->getRelativePath($output)).'**';
            }
        } else {
            $output = $directory;
        }

        // Start
        try {
            $this->dispatcher->notify(new ProcessingStarted());

            try {
                $fs    = new FileSystem($this->dispatcher, $this->caster, $this->adapter, $directory, $output);
                $files = match (true) {
                    default                    => $fs->getFilesIterator($directory, $this->include(), $exclude),
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

                $this->execute($fs, $files, new Globs($directory, $exclude));
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
    protected function execute(FileSystem $fs, iterable $files, Matcher $exclude): void {
        $executor = new Executor($this->dispatcher, $this->tasks, $fs, $files, $exclude);

        $executor->run();
    }

    /**
     * @return list<string>
     */
    protected function include(): array {
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
