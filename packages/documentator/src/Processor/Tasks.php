<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use IteratorAggregate;
use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Override;
use Traversable;
use WeakMap;

use function array_diff_uassoc;
use function array_keys;
use function is_a;
use function is_array;

/**
 * @implements IteratorAggregate<int, class-string<Task>>
 */
class Tasks implements IteratorAggregate {
    /**
     * @var Instances<Task>
     */
    private Instances $instances;

    /**
     * @var array<non-empty-string, GlobMatcher>
     */
    private array $globs = [];

    /**
     * @var WeakMap<File|Hook, list<Hook|non-empty-string>>
     */
    private WeakMap $tags;

    public function __construct(ContainerResolver $container) {
        $this->tags      = new WeakMap();
        $this->instances = new Instances($container, SortOrder::Asc);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIterator(): Traversable {
        yield from $this->instances->classes();
    }

    /**
     * @return list<non-empty-string>
     */
    public function globs(): array {
        return array_keys($this->globs);
    }

    public function has(File|Hook $object): bool {
        return $this->instances->has(...$this->tags($object));
    }

    /**
     * @return iterable<int, Task>
     */
    public function get(File|Hook $object): iterable {
        return $this->instances->get(...$this->tags($object));
    }

    /**
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function add(Task|string $task, ?int $priority = null): void {
        $tags = [];

        if (is_a($task, FileTask::class, true)) {
            foreach ((array) $task::glob() as $tag) {
                $tags[] = $tag;

                if (!isset($this->globs[$tag])) {
                    $this->globs[$tag] = new GlobMatcher($tag);
                }
            }
        }

        if (is_a($task, HookTask::class, true)) {
            $hooks = $task::hook();
            $hooks = is_array($hooks) ? $hooks : [$hooks];

            foreach ($hooks as $hook) {
                $tags[] = $hook;
            }
        }

        $this->instances->add($task, $tags, $priority);
    }

    /**
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function remove(Task|string $task): void {
        // Task
        $this->instances->remove($task);

        // Tags
        $this->tags = new WeakMap();

        // Globs
        $tags = array_diff_uassoc(
            array_keys($this->globs),
            $this->instances->tags(),
            static function (mixed $a, mixed $b): int {
                return $a === $b ? 0 : 1;
            },
        );

        foreach ($tags as $tag) {
            unset($this->globs[$tag]);
        }
    }

    public function reset(): void {
        $this->instances->reset();
    }

    /**
     * @return list<Hook|non-empty-string>
     */
    private function tags(Hook|File $object): array {
        if (!isset($this->tags[$object])) {
            $tags = [];

            if ($object instanceof File) {
                $tags[] = Hook::File;

                foreach ($this->globs as $tag => $matcher) {
                    if ($matcher->match($object->name)) {
                        $tags[] = $tag;
                    }
                }
            } else {
                $tags[] = $object;
            }

            $this->tags[$object] = $tags;
        }

        return $this->tags[$object];
    }
}
