<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\Path\FilePath;
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
        $aFile = Mockery::mock(File::class, [new FilePath('/file.md'), Mockery::mock(Caster::class)]);
        $bFile = Mockery::mock(File::class, [new FilePath('/file.task'), Mockery::mock(Caster::class)]);

        self::assertFalse($tasks->has($aFile));
        self::assertFalse($tasks->has(Hook::File));
        self::assertFalse($tasks->has($bFile));
        self::assertFalse($tasks->has(Hook::BeforeProcessing));

        $tasks->add(new TasksTest__FileTask());
        $tasks->add(TasksTest__HookTask::class);
        $tasks->add(TasksTest__Task::class);

        self::assertTrue($tasks->has($aFile));
        self::assertTrue($tasks->has(Hook::File));
        self::assertTrue($tasks->has($bFile));
        self::assertTrue($tasks->has(Hook::BeforeProcessing));
    }

    public function testGet(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));
        $taskA = new TasksTest__FileTask();
        $taskB = new TasksTest__HookTask();
        $taskC = new TasksTest__Task();
        $taskD = new class() implements FileTask {
            #[Override]
            public static function glob(): string {
                return '*.md';
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file): void {
                // empty
            }
        };
        $aFile = Mockery::mock(File::class, [new FilePath('/file.md'), Mockery::mock(Caster::class)]);
        $bFile = Mockery::mock(File::class, [new FilePath('/file.task'), Mockery::mock(Caster::class)]);

        $tasks->add($taskD, 200);
        $tasks->add($taskA, 100);
        $tasks->add($taskB);
        $tasks->add($taskC);

        self::assertSame(
            [
                $taskA,
                $taskD,
                $taskB,
            ],
            iterator_to_array($tasks->get($aFile), false),
        );
        self::assertSame(
            [
                $taskB,
            ],
            iterator_to_array($tasks->get(Hook::File), false),
        );
        self::assertSame(
            [
                $taskB,
                $taskC,
            ],
            iterator_to_array($tasks->get($bFile), false),
        );
        self::assertSame(
            [
                $taskC,
            ],
            iterator_to_array($tasks->get(Hook::BeforeProcessing), false),
        );
    }

    public function testAdd(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));

        $tasks->add(TasksTest__Task::class);

        self::assertSame(
            [TasksTest__Task::class],
            iterator_to_array($tasks, false),
        );
    }

    public function testRemove(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));

        $tasks->add(new TasksTest__FileTask());
        $tasks->add(TasksTest__HookTask::class);
        $tasks->add(TasksTest__Task::class);

        self::assertTrue($tasks->has(Hook::File));
        self::assertSame(
            ['*.txt', '*.md', '*.task'],
            $tasks->globs(),
        );

        $tasks->remove(TasksTest__HookTask::class);
        $tasks->remove(TasksTest__Task::class);

        self::assertFalse($tasks->has(Hook::File));
        self::assertSame(
            [TasksTest__FileTask::class],
            iterator_to_array($tasks, false),
        );
        self::assertSame(
            ['*.txt', '*.md'],
            $tasks->globs(),
        );
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

    public function testGlobs(): void {
        $tasks = new Tasks(Mockery::mock(ContainerResolver::class));

        $tasks->add(new TasksTest__FileTask());
        $tasks->add(TasksTest__Task::class);

        self::assertSame(
            ['*.txt', '*.md', '*.task'],
            $tasks->globs(),
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
    public static function glob(): array|string {
        return ['*.txt', '*.md'];
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
    public static function hook(): array|Hook {
        return Hook::File;
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TasksTest__Task implements FileTask, HookTask {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function hook(): array|Hook {
        return [Hook::BeforeProcessing];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return ['*.task'];
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file, ?Hook $hook = null): void {
        // empty
    }
}
