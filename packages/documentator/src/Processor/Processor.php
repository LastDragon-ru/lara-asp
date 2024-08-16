<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Symfony\Component\Finder\Glob;

use function array_map;
use function microtime;

class Processor {
    /**
     * @var InstanceList<Task>
     */
    private InstanceList $tasks;

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
     * @param Task|class-string<Task> $task
     */
    public function task(Task|string $task): static {
        $this->tasks->add($task);

        return $this;
    }

    /**
     * @param array<array-key, string>|string|null                              $exclude glob(s) to exclude.
     * @param Closure(string $path, Result $result, float $duration): void|null $listener
     */
    public function run(string $path, array|string|null $exclude = null, ?Closure $listener = null): float {
        $start      = microtime(true);
        $extensions = !$this->tasks->has('*')
            ? array_map(static fn ($e) => "*.{$e}", $this->tasks->keys())
            : null;
        $exclude    = array_map(Glob::toRegex(...), (array) $exclude);
        $root       = new Directory($path, true);
        $fs         = new FileSystem();

        try {
            $iterator = $fs->getFilesIterator($root, patterns: $extensions, exclude: $exclude);
            $executor = new Executor($fs, $root, $exclude, $this->tasks, $iterator, $listener);

            $executor->run();
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($root, $exception);
        }

        return microtime(true) - $start;
    }
}
