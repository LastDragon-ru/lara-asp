<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Output;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Memory;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function mb_rtrim;

/**
 * @internal
 */
#[CoversClass(Listener::class)]
final class ListenerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testInvoke(): void {
        $formatter = new Formatter();
        $output    = new class(80, "\n") extends Output {
            public string $buffer = '';

            #[Override]
            public function write(string $line, Verbosity $verbosity): void {
                $verbosity = match ($verbosity) {
                    Verbosity::Debug       => 'D  | ',
                    Verbosity::Normal      => 'N  | ',
                    Verbosity::Verbose     => 'V  | ',
                    Verbosity::VeryVerbose => 'VV | ',
                };

                $this->buffer .= mb_rtrim($verbosity.$line).$this->eol;
            }
        };
        $listener  = new class($output, $formatter) extends Listener {
            public float   $time   = 0;
            public ?Memory $memory = null;

            #[Override]
            protected function time(): float {
                return $this->time;
            }

            #[Override]
            protected function memory(): Memory {
                return $this->memory ??= new Memory();
            }
        };

        /** @var list<array{float, Event}> $events */
        $events = include_once self::getTestData()->path('~Events.php');

        foreach ($events as [$time, $event]) {
            if ($event instanceof ProcessBegin) {
                $listener->memory = new ListenerTest__Memory(0, 0);
            }

            if ($event instanceof ProcessEnd) {
                $listener->memory = new ListenerTest__Memory(0, 8 * 1024 * 1024);
            }

            $listener->time = $time;

            $listener($event);
        }

        self::assertSame(
            self::getTestData()->content('~Output.txt'),
            $output->buffer,
        );
    }

    /**
     * @param array{Mark, string} $expected
     */
    #[DataProvider('dataProviderPath')]
    public function testPath(
        array $expected,
        DirectoryPath $in,
        DirectoryPath $out,
        DirectoryPath|FilePath $path,
    ): void {
        $output   = new class() extends Output {
            #[Override]
            public function write(string $line, Verbosity $verbosity): void {
                // empty
            }
        };
        $listener = new class ($output, new Formatter()) extends Listener {
            /**
             * @inheritDoc
             */
            #[Override]
            public function path(FilePath|DirectoryPath $path): array {
                return parent::path($path);
            }
        };

        $listener(new ProcessBegin($in, $out, [], []));

        $actual = $listener->path($path);

        self::assertSame($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array{Mark, string}, DirectoryPath, DirectoryPath, DirectoryPath|FilePath}>
     */
    public static function dataProviderPath(): array {
        $a = (new DirectoryPath(self::getTestData()->path('a')))->normalized();
        $b = (new DirectoryPath(self::getTestData()->path('b')))->normalized();

        return [
            '(a, b): in file'            => [
                [Mark::Input, 'a.txt'],
                $a,
                $b,
                new FilePath('../a/a.txt'),
            ],
            '(a, b): out file'           => [
                [Mark::Output, 'b.txt'],
                $a,
                $b,
                new FilePath('../b/b.txt'),
            ],
            '(a, b): external file'      => [
                [Mark::External, (new FilePath(self::getTestData()->path('c.txt')))->normalized()->path],
                $a,
                $b,
                new FilePath('../c.txt'),
            ],
            '(a, a): in file'            => [
                [Mark::Inout, 'a.txt'],
                $a,
                $a,
                new FilePath('../a/a.txt'),
            ],
            '(a, a): external file'      => [
                [Mark::External, (new FilePath(self::getTestData()->path('c.txt')))->normalized()->path],
                $a,
                $a,
                new FilePath('../c.txt'),
            ],
            '(a, b): in directory'       => [
                [Mark::Input, 'a/'],
                $a,
                $b,
                new DirectoryPath('../a/a'),
            ],
            '(a, b): out directory'      => [
                [Mark::Output, 'b/'],
                $a,
                $b,
                new DirectoryPath('../b/b'),
            ],
            '(a, b): external directory' => [
                [Mark::External, (new DirectoryPath(__DIR__))->normalized()->path],
                $a,
                $b,
                new DirectoryPath(__DIR__),
            ],
            '(a, a): in directory'       => [
                [Mark::Inout, 'a/'],
                $a,
                $a,
                new DirectoryPath('../a/a'),
            ],
            '(a, a): external directory' => [
                [Mark::External, (new DirectoryPath(__DIR__))->normalized()->path],
                $a,
                $a,
                new DirectoryPath(__DIR__),
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
readonly class ListenerTest__Memory extends Memory {
    public function __construct(
        protected int $currentMemory,
        protected int $peakMemory,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function current(): int {
        return $this->currentMemory;
    }

    #[Override]
    protected function peak(): int {
        return $this->peakMemory;
    }
}
