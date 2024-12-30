<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\MetadataStorage;
use Symfony\Component\Finder\Glob;

use function array_map;
use function array_merge;
use function microtime;

/**
 * Perform one or more task on the file.
 */
class Processor {
    /**
     * @var InstanceList<Task>
     */
    private InstanceList $tasks;

    /**
     * @var array<array-key, string>
     */
    private array $exclude = [];

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        $this->tasks = new InstanceList($container, $this->key(...));
    }

    /**
     * @param Task|class-string<Task> $task
     *
     * @return list<string>
     */
    private function key(Task|string $task): array {
        return $task::getExtensions();
    }

    /**
     * @return list<Task>
     */
    public function tasks(): array {
        return $this->tasks->instances();
    }

    /**
     * @template T of Task
     *
     * @param InstanceFactory<covariant T>|T|class-string<T> $task
     */
    public function task(InstanceFactory|Task|string $task): static {
        if ($task instanceof InstanceFactory) {
            $this->tasks->add(
                $task->class,   // @phpstan-ignore argument.type (https://github.com/phpstan/phpstan/issues/7609)
                $task->factory, // @phpstan-ignore argument.type (https://github.com/phpstan/phpstan/issues/7609)
            );
        } else {
            $this->tasks->add($task);
        }

        return $this;
    }

    /**
     * @param array<array-key, string>|string $exclude glob(s) to exclude.
     */
    public function exclude(array|string $exclude): static {
        $this->exclude = array_merge($this->exclude, (array) $exclude);

        return $this;
    }

    /**
     * @param Closure(FilePath $input, Result $result, float $duration): void|null $listener
     */
    public function run(
        DirectoryPath|FilePath $input,
        ?DirectoryPath $output = null,
        ?Closure $listener = null,
    ): float {
        $start = microtime(true);
        $depth = match (true) {
            $input instanceof FilePath => 0,
            default                    => null,
        };
        $extensions = match (true) {
            $input instanceof FilePath => $input->getName(),
            !$this->tasks->has('*')    => array_map(static fn ($e) => "*.{$e}", $this->tasks->keys()),
            default                    => null,
        };
        $exclude = array_map(Glob::toRegex(...), $this->exclude);

        try {
            $filesystem = new FileSystem(new MetadataStorage($this->container), $input->getDirectoryPath(), $output);
            $iterator   = $filesystem->getFilesIterator($filesystem->input, $extensions, $depth, $exclude);
            $executor   = new Executor($filesystem, $exclude, $this->tasks, $iterator, $listener);

            $executor->run();
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($exception);
        }

        return microtime(true) - $start;
    }
}
