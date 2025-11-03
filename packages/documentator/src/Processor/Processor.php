<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\GlobMatcher\GlobUtils;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Globs;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;

use function array_map;
use function array_merge;
use function array_values;

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
        protected readonly FileSystemAdapter $adapter,
    ) {
        $this->tasks      = new Tasks($container);
        $this->caster     = new Caster($container, $this->adapter);
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @internal
     * @return iterable<array-key, Task>
     */
    public function getTasks(): iterable {
        return $this->tasks->get();
    }

    /**
     * The first added tasks have a bigger priority.
     *
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function addTask(Task|string $task, ?int $priority = null): static {
        $extensions = $task::getExtensions();
        $extensions = array_map(
            static function (Hook|string $extension): string {
                return $extension instanceof Hook ? $extension->value : $extension;
            },
            $extensions,
        );

        $this->tasks->add($task, $extensions, $priority);

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
        $depth = match (true) {
            $input instanceof FilePath => 0,
            default                    => null,
        };
        $extensions = match (true) {
            $input instanceof FilePath => [$input->getName()],
            !$this->tasks->has('*')    => array_map(static fn ($e) => "**/*.{$e}", $this->tasks->getTags()),
            default                    => [],
        };
        $include   = $extensions;
        $exclude   = $this->exclude;
        $directory = $input->getDirectoryPath('.');

        // If `$output` specified and inside `$input` we should not process it.
        if ($output !== null) {
            if (!$directory->isEqual($output) && $directory->isInside($output)) {
                $exclude[] = GlobUtils::escape((string) $directory->getRelativePath($output)).'**';
            }
        } else {
            $output = $directory;
        }

        // Start
        try {
            $this->dispatcher->notify(new ProcessingStarted());

            try {
                $this->execute($directory, $output, $include, $exclude, $depth);
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
     * @param list<string> $include
     * @param list<string> $exclude
     */
    protected function execute(
        DirectoryPath $input,
        DirectoryPath $output,
        array $include,
        array $exclude,
        ?int $depth,
    ): void {
        $filesystem = new FileSystem($this->dispatcher, $this->caster, $this->adapter, $input, $output);
        $iterator   = new Iterator($filesystem, $filesystem->getFilesIterator($input, $include, $exclude, $depth));
        $executor   = new Executor($this->dispatcher, $this->tasks, $filesystem, $iterator, new Globs($exclude));

        $executor->run();
    }
}
