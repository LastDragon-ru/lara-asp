<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Symfony\Component\Finder\Glob;

use function array_keys;
use function array_map;
use function array_unique;
use function in_array;
use function microtime;

class Processor {
    /**
     * @var array<string, list<Task>>
     */
    private array $tasks = [];

    public function __construct() {
        // empty
    }

    public function task(Task $task): static {
        foreach (array_unique($task->getExtensions()) as $ext) {
            if (!isset($this->tasks[$ext])) {
                $this->tasks[$ext] = [];
            }

            if (!in_array($task, $this->tasks[$ext], true)) {
                $this->tasks[$ext][] = $task;
            }
        }

        return $this;
    }

    /**
     * @param array<array-key, string>|string|null                              $exclude glob(s) to exclude.
     * @param Closure(string $path, Result $result, float $duration): void|null $listener
     */
    public function run(string $path, array|string|null $exclude = null, ?Closure $listener = null): float {
        $start      = microtime(true);
        $extensions = !isset($this->tasks['*'])
            ? array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks))
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
