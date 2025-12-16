<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Package\RawOutputFormatter;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function array_walk_recursive;
use function assert;

/**
 * @internal
 */
#[CoversClass(Listener::class)]
final class ListenerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param OutputInterface::VERBOSITY_* $verbosity
     * @param list<Event>                  $events
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, int $verbosity, array $events): void {
        $formatter = Mockery::mock($this->app()->make(Formatter::class));
        $formatter->makePartial();
        $formatter
            ->shouldReceive('duration')
            ->andReturn('00:00:00.000'); // todo(documentator): would be nice to test time calculation too
        $formatter
            ->shouldReceive('filesize')
            ->andReturn('14.00 MiB'); // todo(documentator): would be nice to test memory usage too

        self::assertInstanceOf(Formatter::class, $formatter);

        $output = new BufferedOutput($verbosity, false, new RawOutputFormatter());
        $writer = new class ($output, $formatter) extends Listener {
            #[Override]
            protected function getTerminalWidth(): int {
                return 80;
            }
        };

        foreach ($events as $event) {
            $writer($event);
        }

        self::assertSame(
            self::getTestData()->content($expected),
            Text::setEol($output->fetch()),
        );
    }

    #[DataProvider('dataProviderGetPathname')]
    public function testGetPathname(
        string $expected,
        ?DirectoryPath $input,
        ?DirectoryPath $output,
        DirectoryPath|FilePath $path,
    ): void {
        $listener = new class () extends Listener {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            #[Override]
            public function pathname(FilePath|DirectoryPath $path): string {
                return parent::pathname($path);
            }
        };

        if ($input !== null || $output !== null) {
            $input  ??= $output;
            $output ??= $input;

            $listener(new ProcessBegin($input, $output));
        }

        self::assertSame($expected, $listener->pathname($path));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, OutputInterface::VERBOSITY_*, list<Event>}>
     */
    public static function dataProviderInvoke(): array {
        $root = new DirectoryPath('/inout');
        $tree = [
            new ProcessBegin($root, $root),
            [
                new FileBegin($root->file('a/a.txt')),
                [
                    new TaskBegin(ListenerTest__TaskA::class),
                    [
                        new DependencyBegin($root->file('b/b/bb.txt')),
                        [
                            new FileBegin($root->file('b/b/bb.txt')),
                            [
                                new TaskBegin(ListenerTest__TaskA::class),
                                [
                                    new DependencyBegin($root->file('b/a/ba.txt')),
                                    [
                                        new FileBegin($root->file('b/a/ba.txt')),
                                        [
                                            new TaskBegin(ListenerTest__TaskA::class),
                                            new TaskEnd(TaskResult::Success),
                                        ],
                                        new FileEnd(FileResult::Success),
                                    ],
                                    new DependencyEnd(DependencyResult::Resolved),
                                    new DependencyBegin($root->file('c.txt')),
                                    [
                                        new FileBegin($root->file('c.txt')),
                                        [
                                            new TaskBegin(ListenerTest__TaskA::class),
                                            [
                                                new DependencyBegin($root->file('c.txt')),
                                                new DependencyEnd(DependencyResult::NotFound),
                                            ],
                                            new TaskEnd(TaskResult::Success),
                                        ],
                                        new FileEnd(FileResult::Success),
                                    ],
                                    new DependencyEnd(DependencyResult::Resolved),
                                    new DependencyBegin($root->file('../../../README.md'),),
                                    [
                                        new FileBegin($root->file('../../../README.md')),
                                        new FileEnd(FileResult::Skipped),
                                    ],
                                    new DependencyEnd(DependencyResult::NotFound),
                                ],
                                new TaskEnd(TaskResult::Success),
                            ],
                            new FileEnd(FileResult::Success),
                        ],
                        new DependencyEnd(DependencyResult::Resolved),
                        new DependencyBegin($root->file('c.txt')),
                        new DependencyEnd(DependencyResult::Resolved),
                        new DependencyBegin($root->file('c.html')),
                        [
                            new FileBegin($root->file('c.html')),
                            new FileEnd(FileResult::Skipped),
                        ],
                        new DependencyEnd(DependencyResult::Resolved),
                        new DependencyBegin($root->file('a/excluded.txt')),
                        [
                            new FileBegin($root->file('a/excluded.txt')),
                            new FileEnd(FileResult::Skipped),
                        ],
                        new DependencyEnd(DependencyResult::Resolved),
                    ],
                    new TaskEnd(TaskResult::Success),
                ],
                new FileEnd(FileResult::Success),
                new FileBegin($root->file('a/a/aa.txt')),
                [
                    new TaskBegin(ListenerTest__TaskA::class),
                    new TaskEnd(TaskResult::Success),
                ],
                new FileEnd(FileResult::Success),
                new FileBegin($root->file('a/b/ab.txt')),
                [
                    new TaskBegin(ListenerTest__TaskA::class),
                    new TaskEnd(TaskResult::Success),
                ],
                new FileEnd(FileResult::Success),
                new FileBegin($root->file('b/b.txt')),
                [
                    new TaskBegin(ListenerTest__TaskA::class),
                    new TaskEnd(TaskResult::Success),
                ],
                new FileEnd(FileResult::Success),
                new FileBegin($root->file('c.htm')),
                [
                    new TaskBegin(ListenerTest__TaskA::class),
                    [
                        new DependencyBegin($root->file('c.htm')),
                        new DependencyEnd(DependencyResult::NotFound),
                        new DependencyBegin($root->file('c.new')),
                        [
                            new FileBegin($root->file('c.new')),
                            new FileEnd(FileResult::Error),
                        ],
                        new DependencyEnd(DependencyResult::Resolved),
                        new DependencyBegin($root->file('c.next')),
                        new DependencyEnd(DependencyResult::Queued),
                    ],
                    new TaskEnd(TaskResult::Success),
                    new TaskBegin(ListenerTest__TaskB::class),
                    [
                        new DependencyBegin($root->file('c.new')),
                        new DependencyEnd(DependencyResult::NotFound),
                    ],
                    new TaskEnd(TaskResult::Success),
                ],
                new FileEnd(FileResult::Success),
            ],
            new ProcessEnd(ProcessResult::Success),
        ];

        $events = [];

        array_walk_recursive($tree, static function (mixed $item) use (&$events): void {
            assert($item instanceof Event); // for phpstan

            $events[] = $item;
        });

        return [
            'No files'    => [
                '~NoFiles.txt',
                OutputInterface::VERBOSITY_NORMAL,
                [
                    new ProcessBegin($root, $root),
                    new ProcessEnd(ProcessResult::Success),
                ],
            ],
            'Normal'      => [
                '~Normal.txt',
                OutputInterface::VERBOSITY_NORMAL,
                $events,
            ],
            'Verbose'     => [
                '~Verbose.txt',
                OutputInterface::VERBOSITY_VERBOSE,
                $events,
            ],
            'VeryVerbose' => [
                '~VeryVerbose.txt',
                OutputInterface::VERBOSITY_VERY_VERBOSE,
                $events,
            ],
            'Debug'       => [
                '~Debug.txt',
                OutputInterface::VERBOSITY_DEBUG,
                $events,
            ],
        ];
    }

    /**
     * @return array<string, array{string, ?DirectoryPath, ?DirectoryPath, DirectoryPath|FilePath}>
     */
    public static function dataProviderGetPathname(): array {
        $a = (new DirectoryPath(self::getTestData()->path('a')))->normalized();
        $b = (new DirectoryPath(self::getTestData()->path('b')))->normalized();

        return [
            '(a, b): in file'                         => [
                '→ a.txt',
                $a,
                $b,
                new FilePath('../a/a.txt'),
            ],
            '(a, b): out file'                        => [
                '← b.txt',
                $a,
                $b,
                new FilePath('../b/b.txt'),
            ],
            '(a, b): external file'                   => [
                '! '.(new FilePath(self::getTestData()->path('c.txt')))->normalized(),
                $a,
                $b,
                new FilePath('../c.txt'),
            ],
            '(a, a): in file'                         => [
                '↔ a.txt',
                $a,
                $a,
                new FilePath('../a/a.txt'),
            ],
            '(a, a): external file'                   => [
                '! '.(new FilePath(self::getTestData()->path('c.txt')))->normalized(),
                $a,
                $a,
                new FilePath('../c.txt'),
            ],
            '(a, b): in directory'                    => [
                '→ a/',
                $a,
                $b,
                new DirectoryPath('../a/a'),
            ],
            '(a, b): out directory'                   => [
                '← b/',
                $a,
                $b,
                new DirectoryPath('../b/b'),
            ],
            '(a, b): external directory'              => [
                '! '.(new DirectoryPath(__DIR__))->normalized(),
                $a,
                $b,
                new DirectoryPath(__DIR__),
            ],
            '(a, a): in directory'                    => [
                '↔ a/',
                $a,
                $a,
                new DirectoryPath('../a/a'),
            ],
            '(a, a): external directory'              => [
                '! '.(new DirectoryPath(__DIR__))->normalized(),
                $a,
                $a,
                new DirectoryPath(__DIR__),
            ],
            '(null): in/out not initialized properly' => [
                '? ../../a.txt',
                null,
                null,
                new FilePath('../../a.txt'),
            ],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ListenerTest__TaskA implements FileTask {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*';
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ListenerTest__TaskB implements FileTask {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*';
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): void {
        // empty;
    }
}
