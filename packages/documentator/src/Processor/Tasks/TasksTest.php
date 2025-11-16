<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Tasks::class)]
final class TasksTest extends TestCase {
    public function testHas(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));
        $file  = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->twice()
            ->andReturn('md');

        self::assertFalse($tasks->has($file));
        self::assertFalse($tasks->has(Hook::File));

        $tasks->add(new TasksTest__FileTask());
        $tasks->add(TasksTest__HookTask::class);

        self::assertTrue($tasks->has($file));
        self::assertTrue($tasks->has(Hook::File));
    }

    public function testGet(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));
        $taskA = new TasksTest__FileTask();
        $taskB = new TasksTest__HookTask();
        $taskC = new class() implements FileTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['md'];
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file): void {
                // empty
            }
        };
        $file  = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn('md');

        $tasks->add($taskC, 200);
        $tasks->add($taskA, 100);
        $tasks->add($taskB);

        self::assertSame(
            [
                $taskA,
                $taskC,
                $taskB,
            ],
            iterator_to_array($tasks->get($file), false),
        );
        self::assertSame(
            [
                $taskB,
            ],
            iterator_to_array($tasks->get(Hook::File), false),
        );
    }

    public function testAdd(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));
        $task  = new TasksTest__FileTask();

        self::assertTrue($tasks->add($task));
    }

    public function testRemove(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));
        $task  = new TasksTest__HookTask();

        $tasks->add($task);

        self::assertTrue($tasks->has(Hook::File));

        $tasks->remove($task::class);

        self::assertFalse($tasks->has(Hook::File));
    }

    public function testGetIterator(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));

        $tasks->add(new TasksTest__FileTask(), 200);
        $tasks->add(TasksTest__HookTask::class, 100);

        self::assertSame(
            [
                TasksTest__HookTask::class,
                TasksTest__FileTask::class,
            ],
            iterator_to_array($tasks, false),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TasksTest__FileTask implements FileTask {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['txt', 'md'];
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TasksTest__HookTask implements HookTask {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function hooks(): array {
        return [Hook::File];
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
        // empty
    }
}
