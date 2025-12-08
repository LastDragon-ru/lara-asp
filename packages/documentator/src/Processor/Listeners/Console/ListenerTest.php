<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Package\RawOutputFormatter;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
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

    /**
     * @param array{FileSystemModifiedType|Flag|null, list<string>} $expected
     * @param list<Change>                                          $changes
     */
    #[DataProvider('dataProviderFlags')]
    public function testFlags(array $expected, array $changes, string $path): void {
        $writer = new class () extends Listener {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function flags(array $changes, string $path, FileSystemModifiedType|Flag|null &$flag): array {
                return parent::flags($changes, $path, $flag);
            }
        };

        $flag  = null;
        $flags = $writer->flags($changes, $path, $flag);

        self::assertEquals($expected, [$flag, $flags]);
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

            $listener(new ProcessingStarted($input, $output));
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
            new ProcessingStarted($root, $root),
            [
                new FileStarted($root->file('a/a.txt')),
                [
                    new TaskStarted(ListenerTest__TaskA::class),
                    [
                        new DependencyResolved($root->file('b/b/bb.txt'), DependencyResolvedResult::Success),
                        [
                            new FileStarted($root->file('b/b/bb.txt')),
                            [
                                new TaskStarted(ListenerTest__TaskA::class),
                                [
                                    new DependencyResolved(
                                        $root->file('b/a/ba.txt'),
                                        DependencyResolvedResult::Success,
                                    ),
                                    [
                                        new FileStarted($root->file('b/a/ba.txt')),
                                        [
                                            new TaskStarted(ListenerTest__TaskA::class),
                                            new TaskFinished(TaskFinishedResult::Success),
                                        ],
                                        new FileFinished(FileFinishedResult::Success),
                                    ],
                                    new DependencyResolved(
                                        $root->file('c.txt'),
                                        DependencyResolvedResult::Success,
                                    ),
                                    [
                                        new FileStarted($root->file('c.txt')),
                                        [
                                            new TaskStarted(ListenerTest__TaskA::class),
                                            [
                                                new FileSystemModified(
                                                    $root->file('c.txt'),
                                                    FileSystemModifiedType::Updated,
                                                ),
                                                new DependencyResolved(
                                                    $root->file('c.txt'),
                                                    DependencyResolvedResult::Null,
                                                ),
                                            ],
                                            new TaskFinished(TaskFinishedResult::Success),
                                        ],
                                        new FileFinished(FileFinishedResult::Success),
                                    ],
                                    new FileSystemModified(
                                        $root->file('../../../README.md'),
                                        FileSystemModifiedType::Created,
                                    ),
                                    new DependencyResolved(
                                        $root->file('../../../README.md'),
                                        DependencyResolvedResult::Null,
                                    ),
                                    [
                                        new FileStarted($root->file('../../../README.md')),
                                        new FileFinished(FileFinishedResult::Skipped),
                                    ],
                                ],
                                new TaskFinished(TaskFinishedResult::Success),
                            ],
                            new FileFinished(FileFinishedResult::Success),
                        ],
                        new DependencyResolved($root->file('c.txt'), DependencyResolvedResult::Success),
                        new DependencyResolved($root->file('c.html'), DependencyResolvedResult::Success),
                        [
                            new FileStarted($root->file('c.html')),
                            new FileFinished(FileFinishedResult::Skipped),
                        ],
                        new DependencyResolved($root->file('a/excluded.txt'), DependencyResolvedResult::Success),
                        [
                            new FileStarted($root->file('a/excluded.txt')),
                            new FileFinished(FileFinishedResult::Skipped),
                        ],
                    ],
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($root->file('a/a/aa.txt')),
                [
                    new TaskStarted(ListenerTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($root->file('a/b/ab.txt')),
                [
                    new TaskStarted(ListenerTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($root->file('b/b.txt')),
                [
                    new TaskStarted(ListenerTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($root->file('c.htm')),
                [
                    new TaskStarted(ListenerTest__TaskA::class),
                    [
                        new FileSystemModified($root->file('c.htm'), FileSystemModifiedType::Updated),
                        new DependencyResolved($root->file('c.htm'), DependencyResolvedResult::Null),
                        new FileSystemModified($root->file('c.new'), FileSystemModifiedType::Created),
                        new DependencyResolved($root->file('c.new'), DependencyResolvedResult::Success),
                        [
                            new FileStarted($root->file('c.new')),
                            new FileFinished(FileFinishedResult::Failed),
                        ],
                        new DependencyResolved($root->file('c.next'), DependencyResolvedResult::Queued),
                    ],
                    new TaskFinished(TaskFinishedResult::Success),
                    new TaskStarted(ListenerTest__TaskB::class),
                    [
                        new FileSystemModified($root->file('c.new'), FileSystemModifiedType::Updated),
                        new DependencyResolved($root->file('c.new'), DependencyResolvedResult::Null),
                    ],
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
            ],
            new ProcessingFinished(ProcessingFinishedResult::Success),
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
                    new ProcessingStarted($root, $root),
                    new ProcessingFinished(ProcessingFinishedResult::Success),
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
     * @return array<string, array{array{FileSystemModifiedType|Flag|null, list<string>}, list<Change>, string}>
     */
    public static function dataProviderFlags(): array {
        return [
            'Path doesn\'t match'            => [
                [null, ['<fg=green>C</>', '<fg=yellow>U</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Updated),
                    new Change('b', FileSystemModifiedType::Updated),
                    new Change('c', FileSystemModifiedType::Created),
                ],
                'd',
            ],
            'Path match to one'              => [
                [FileSystemModifiedType::Updated, ['<fg=green>C</>', '<fg=yellow>U</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('b', FileSystemModifiedType::Updated),
                    new Change('c', FileSystemModifiedType::Created),
                ],
                'b',
            ],
            'Path match to multiple (same)'  => [
                [FileSystemModifiedType::Created, ['<fg=green>C</>', '<fg=yellow>U</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('b', FileSystemModifiedType::Updated),
                ],
                'a',
            ],
            'Path match to multiple (mixed)' => [
                [Flag::Mixed, ['<fg=green>C</>', '<fg=yellow>U</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Updated),
                    new Change('b', FileSystemModifiedType::Updated),
                ],
                'a',
            ],
            'Path only (same)'               => [
                [FileSystemModifiedType::Created, ['<fg=green>C</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Created),
                ],
                'a',
            ],
            'Path only (mixed)'              => [
                [Flag::Mixed, ['<fg=green>C</>', '<fg=yellow>U</>']],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Updated),
                ],
                'a',
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
