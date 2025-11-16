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

use function array_map;
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
        yield from $this->instances->getClasses();
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
    public function remove(Task|string $task): bool {
        $this->instances->remove($task);

        return true;
    }

    /**
     * @param Task|Hook|File|class-string<Task> $object
     *
     * @return list<string>
     */
    private function tags(Task|Hook|File|string $object): array {
        return match (true) {
            $object instanceof File              => [
                $this->hook(Hook::File),
                $this->file((string) $object->getExtension()),
                $this->file('*'),
            ],
            $object instanceof Hook              => [$this->hook($object)],
            is_a($object, HookTask::class, true) => array_map($this->hook(...), $object::hooks()),
            is_a($object, FileTask::class, true) => array_map($this->file(...), $object::getExtensions()),
            default                              => [],
        };
    }

    private function hook(Hook $hook): string {
        return HookTask::class.': '.$hook->name;
    }

    private function file(string $ext): string {
        return FileTask::class.': '.$ext;
    }
}
