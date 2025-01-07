<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
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
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\RawOutputFormatter;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use LastDragon_ru\LaraASP\Formatter\Formatter;
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
#[CoversClass(Writer::class)]
final class WriterTest extends TestCase {
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
        $writer = new class ($output, $formatter) extends Writer {
            #[Override]
            protected function getTerminalWidth(): int {
                return 80;
            }
        };

        foreach ($events as $event) {
            $writer($event);
        }

        self::assertEquals(
            self::getTestData()->content($expected),
            Text::setEol($output->fetch()),
        );
    }

    /**
     * @param array{FileSystemModifiedType|Flag|null, string} $expected
     * @param list<Change>                                    $changes
     */
    #[DataProvider('dataProviderFlags')]
    public function testFlags(array $expected, array $changes, string $path): void {
        $writer = new class () extends Writer {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function flags(array $changes, string $path, FileSystemModifiedType|Flag|null &$flag): string {
                return parent::flags($changes, $path, $flag);
            }
        };

        $flag  = null;
        $flags = $writer->flags($changes, $path, $flag);

        self::assertEquals($expected, [$flag, $flags]);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, OutputInterface::VERBOSITY_*, list<Event>}>
     */
    public static function dataProviderInvoke(): array {
        $tree = [
            new ProcessingStarted(),
            [
                new FileStarted('↔ a/a.txt'),
                [
                    new TaskStarted(WriterTest__TaskA::class),
                    [
                        new DependencyResolved('↔ b/b/bb.txt', DependencyResolvedResult::Success),
                        [
                            new FileStarted('↔ b/b/bb.txt'),
                            [
                                new TaskStarted(WriterTest__TaskA::class),
                                [
                                    new DependencyResolved('↔ b/a/ba.txt', DependencyResolvedResult::Success),
                                    [
                                        new FileStarted('↔ b/a/ba.txt'),
                                        [
                                            new TaskStarted(WriterTest__TaskA::class),
                                            new TaskFinished(TaskFinishedResult::Success),
                                        ],
                                        new FileFinished(FileFinishedResult::Success),
                                    ],
                                    new DependencyResolved('↔ c.txt', DependencyResolvedResult::Success),
                                    [
                                        new FileStarted('↔ c.txt'),
                                        [
                                            new TaskStarted(WriterTest__TaskA::class),
                                            [
                                                new FileSystemModified('↔ c.txt', FileSystemModifiedType::Updated),
                                                new DependencyResolved('↔ c.txt', DependencyResolvedResult::Null),
                                            ],
                                            new TaskFinished(TaskFinishedResult::Success),
                                        ],
                                        new FileFinished(FileFinishedResult::Success),
                                    ],
                                    new FileSystemModified('↔ ../../../README.md', FileSystemModifiedType::Created),
                                    new DependencyResolved('↔ ../../../README.md', DependencyResolvedResult::Null),
                                    [
                                        new FileStarted('↔ ../../../README.md'),
                                        new FileFinished(FileFinishedResult::Skipped),
                                    ],
                                ],
                                new TaskFinished(TaskFinishedResult::Success),
                            ],
                            new FileFinished(FileFinishedResult::Success),
                        ],
                        new DependencyResolved('↔ c.txt', DependencyResolvedResult::Success),
                        new DependencyResolved('↔ c.html', DependencyResolvedResult::Success),
                        [
                            new FileStarted('↔ c.html'),
                            new FileFinished(FileFinishedResult::Skipped),
                        ],
                        new DependencyResolved('↔ a/excluded.txt', DependencyResolvedResult::Success),
                        [
                            new FileStarted('↔ a/excluded.txt'),
                            new FileFinished(FileFinishedResult::Skipped),
                        ],
                    ],
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/a/aa.txt'),
                [
                    new TaskStarted(WriterTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/b/ab.txt'),
                [
                    new TaskStarted(WriterTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/b.txt'),
                [
                    new TaskStarted(WriterTest__TaskA::class),
                    new TaskFinished(TaskFinishedResult::Success),
                ],
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ c.htm'),
                [
                    new TaskStarted(WriterTest__TaskA::class),
                    [
                        new FileSystemModified('↔ c.htm', FileSystemModifiedType::Updated),
                        new DependencyResolved('↔ c.htm', DependencyResolvedResult::Null),
                        new FileSystemModified('↔ c.new', FileSystemModifiedType::Created),
                        new DependencyResolved('↔ c.new', DependencyResolvedResult::Success),
                        [
                            new FileStarted('↔ c.new'),
                            new FileFinished(FileFinishedResult::Failed),
                        ],
                    ],
                    new TaskFinished(TaskFinishedResult::Success),
                    new TaskStarted(WriterTest__TaskB::class),
                    [
                        new FileSystemModified('↔ c.new', FileSystemModifiedType::Updated),
                        new DependencyResolved('↔ c.new', DependencyResolvedResult::Null),
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
                    new ProcessingStarted(),
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
     * @return array<string, array{array{FileSystemModifiedType|Flag|null, string}, list<Change>, string}>
     */
    public static function dataProviderFlags(): array {
        return [
            'Path doesn\'t match'            => [
                [null, '<fg=green>C</><fg=yellow>U</>'],
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
                [FileSystemModifiedType::Updated, '<fg=green>C</><fg=yellow>U</>'],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('b', FileSystemModifiedType::Updated),
                    new Change('c', FileSystemModifiedType::Created),
                ],
                'b',
            ],
            'Path match to multiple (same)'  => [
                [FileSystemModifiedType::Created, '<fg=green>C</><fg=yellow>U</>'],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('b', FileSystemModifiedType::Updated),
                ],
                'a',
            ],
            'Path match to multiple (mixed)' => [
                [Flag::Mixed, '<fg=green>C</><fg=yellow>U</>'],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Updated),
                    new Change('b', FileSystemModifiedType::Updated),
                ],
                'a',
            ],
            'Path only (same)'               => [
                [FileSystemModifiedType::Created, ''],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Created),
                ],
                'a',
            ],
            'Path only (mixed)'              => [
                [Flag::Mixed, '<fg=green>C</><fg=yellow>U</>'],
                [
                    new Change('a', FileSystemModifiedType::Created),
                    new Change('a', FileSystemModifiedType::Updated),
                ],
                'a',
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
class WriterTest__TaskA implements Task {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function __invoke(File $file): bool {
        return true;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WriterTest__TaskB implements Task {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function __invoke(File $file): bool {
        return true;
    }
}
