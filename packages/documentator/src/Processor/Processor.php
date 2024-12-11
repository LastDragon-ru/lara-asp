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
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
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

    public function __construct(ContainerResolver $container) {
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
     * @param Task|class-string<Task>                         $task
     * @param ($task is object ? null : ?Closure(Task): void) $configurator
     */
    public function task(Task|string $task, ?Closure $configurator = null): static {
        $this->tasks->add($task, $configurator);

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
     * @param Closure(FilePath $path, Result $result, float $duration): void|null $listener
     */
    public function run(
        DirectoryPath|FilePath $path,
        ?Closure $listener = null,
    ): float {
        $start = microtime(true);
        $depth = match (true) {
            $path instanceof FilePath => 0,
            default                   => null,
        };
        $extensions = match (true) {
            $path instanceof FilePath => $path->getName(),
            !$this->tasks->has('*')   => array_map(static fn ($e) => "*.{$e}", $this->tasks->keys()),
            default                   => null,
        };
        $exclude = array_map(Glob::toRegex(...), $this->exclude);
        $root    = new Directory($path->getDirectoryPath(), true);
        $fs      = new FileSystem();

        try {
            $iterator = $fs->getFilesIterator($root, $extensions, $depth, $exclude);
            $executor = new Executor($fs, $root, $exclude, $this->tasks, $iterator, $listener);

            $executor->run();
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($path, $exception);
        }

        return microtime(true) - $start;
    }
}
