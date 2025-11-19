<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Override;
use Traversable;

use function is_a;

/**
 * @implements IteratorAggregate<int, class-string<Task>>
 */
class Tasks implements IteratorAggregate {
    /**
     * @var Instances<Task>
     */
    private Instances $instances;

    public function __construct(ContainerResolver $container) {
        $this->instances = new Instances($container, SortOrder::Asc);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIterator(): Traversable {
        yield from $this->instances->classes();
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
    public function add(Task|string $task, ?int $priority = null): bool {
        $tags = $this->tags($task);

        if ($tags !== []) {
            $this->instances->add($task, $tags, $priority);

            return true;
        }

        return false;
    }

    /**
     * @template T of Task
     *
     * @param T|class-string<T> $task
     */
    public function remove(Task|string $task): void {
        $this->instances->remove($task);
    }

    public function reset(): void {
        $this->instances->reset();
    }

    /**
     * @param Task|Hook|File|class-string<Task> $object
     *
     * @return list<Hook|string|null>
     */
    private function tags(Task|Hook|File|string $object): array {
        return match (true) {
            $object instanceof File              => [Hook::File, (string) $object->getExtension(), '*'],
            $object instanceof Hook              => [$object],
            is_a($object, HookTask::class, true) => $object::hooks(),
            is_a($object, FileTask::class, true) => $object::getExtensions(),
            default                              => [],
        };
    }
}
