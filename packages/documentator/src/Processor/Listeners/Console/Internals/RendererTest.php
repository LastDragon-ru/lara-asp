<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Defaults\Output;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Status;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param positive-int $columns
     * @param int<0, max>  $padding
     */
    #[DataProvider('dataProviderTitle')]
    public function testTitle(
        string $expected,
        int $columns,
        Message|string $title,
        int $padding,
        ?Mark $mark,
    ): void {
        $formatter = new Formatter();
        $output    = new Output($columns, "\n");
        $lines     = (new Renderer($output, $formatter))->title($title, $padding, $mark);
        $actual    = implode("\n", iterator_to_array($lines, false));

        self::assertSame($expected, $actual);
    }

    /**
     * @param positive-int                                         $columns
     * @param iterable<mixed, array{Mark, Message|string, string}> $properties
     * @param int<0, max>                                          $padding
     */
    #[DataProvider('dataProviderProperties')]
    public function testProperties(
        string $expected,
        int $columns,
        iterable $properties,
        int $padding,
    ): void {
        $formatter = new Formatter();
        $output    = new Output($columns, "\n");
        $lines     = (new Renderer($output, $formatter))->properties($properties, $padding);
        $actual    = implode("\n", iterator_to_array($lines, false));

        self::assertSame($expected, $actual);
    }

    /**
     * @param positive-int $columns
     * @param int<0, max>  $padding
     * @param ?list<Flag>  $flags
     */
    #[DataProvider('dataProviderRun')]
    public function testRun(
        string $expected,
        int $columns,
        Message|string $title,
        int $padding,
        ?Mark $mark,
        ?string $value,
        ?array $flags,
        ?Status $status,
        ?float $duration,
    ): void {
        $formatter = new Formatter();
        $output    = new Output($columns, "\n");
        $renderer  = new Renderer($output, $formatter);
        $lines     = $renderer->run($title, $padding, $mark, $value, $flags, $status, $duration);
        $actual    = implode("\n", iterator_to_array($lines, false));

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, positive-int, Message|string, int<0, max>, ?Mark}>
     */
    public static function dataProviderTitle(): array {
        return [
            'Message'                         => [
                <<<'TXT'
                Processing
                TXT,
                50,
                Message::Title,
                0,
                null,
            ],
            'Short text, no mark, no padding' => [
                <<<'TXT'
                Text text text text text text
                TXT,
                50,
                'Text text text text text text',
                0,
                null,
            ],
            'Short text, mark, no padding'    => [
                <<<'TXT'
                ● Text text text text text text
                TXT,
                50,
                'Text text text text text text',
                0,
                Mark::Task,
            ],
            'Short text, no mark, padding'    => [
                <<<'TXT'
                · · Text text text text text text
                TXT,
                50,
                'Text text text text text text',
                2,
                null,
            ],
            'Short text, mark, padding'       => [
                <<<'TXT'
                · ? Text text text text text text
                TXT,
                50,
                'Text text text text text text',
                1,
                Mark::Info,
            ],
            'Long text, no mark, no padding'  => [
                <<<'TXT'
                Text text text t
                ext text text
                TXT,
                16,
                'Text text text text text text',
                0,
                null,
            ],
            'Long text, mark, no padding'     => [
                <<<'TXT'
                ● Text text text
                ·  text text tex
                · t
                TXT,
                16,
                'Text text text text text text',
                0,
                Mark::Task,
            ],
            'Long text, no mark, padding'     => [
                <<<'TXT'
                · · Text text te
                · · xt text text
                · ·  text
                TXT,
                16,
                'Text text text text text text',
                2,
                null,
            ],
            'Long text, mark, padding'        => [
                <<<'TXT'
                · ? Text text te
                · · xt text text
                · ·  text
                TXT,
                16,
                'Text text text text text text',
                1,
                Mark::Info,
            ],
        ];
    }

    /**
     * @return array<string, array{string, positive-int, iterable<mixed, array{Mark, Message, string}>, int<0, max>}>
     */
    public static function dataProviderProperties(): array {
        return [
            'Empty'              => [
                '',
                50,
                [
                    // empty
                ],
                0,
            ],
            'Short'              => [
                <<<'TXT'
                ? Output  value
                ? Exclude value
                TXT,
                50,
                [
                    [Mark::Info, Message::Output, 'value'],
                    [Mark::Info, Message::Exclude, 'value'],
                ],
                0,
            ],
            'Short with padding' => [
                <<<'TXT'
                · ? Output  value
                · ? Exclude value
                TXT,
                50,
                [
                    [Mark::Info, Message::Output, 'value'],
                    [Mark::Info, Message::Exclude, 'value'],
                ],
                1,
            ],
            'Long'               => [
                <<<'TXT'
                ? Output  value value
                ·          value valu
                ·         e value
                ? Exclude value value
                ·          value valu
                ·         e value
                TXT,
                21,
                [
                    [Mark::Info, Message::Output, 'value value value value value'],
                    [Mark::Info, Message::Exclude, 'value value value value value'],
                ],
                0,
            ],
            'Long with padding'  => [
                <<<'TXT'
                · ? Exclude value value val
                · ·         ue value value
                · ? Output  value value val
                · ·         ue value value
                TXT,
                27,
                [
                    [Mark::Info, Message::Exclude, 'value value value value value'],
                    [Mark::Info, Message::Output, 'value value value value value'],
                ],
                1,
            ],
        ];
    }

    /**
     * @return array<string, array{string, positive-int, Message|string, int<0, max>, ?Mark, ?string, ?list<Flag>, ?Status, ?float}>
     */
    public static function dataProviderRun(): array {
        return [
            'Short'                             => [
                <<<'TXT'
                ● Title ..................... .W.   123.450 s DONE
                TXT,
                50,
                'Title',
                0,
                Mark::Task,
                null,
                [Flag::Write],
                Status::Done,
                123.45,
            ],
            'Short with padding'                => [
                <<<'TXT'
                · ● Title ................... RW.   123.450 s DONE
                TXT,
                50,
                'Title',
                1,
                Mark::Task,
                null,
                [Flag::Write, Flag::Read],
                Status::Done,
                123.45,
            ],
            'Long'                              => [
                <<<'TXT'
                ● Title title title title tit
                · le title title ............ ..D   123.450 s DONE
                TXT,
                50,
                'Title title title title title title title',
                0,
                Mark::Task,
                null,
                [Flag::Delete],
                Status::Done,
                123.45,
            ],
            'Long with padding'                 => [
                <<<'TXT'
                · ● Title title title title t
                · · itle title title ........ ...   123.450 s DONE
                TXT,
                50,
                'Title title title title title title title',
                1,
                Mark::Task,
                null,
                [],
                Status::Done,
                123.45,
            ],
            'Equal (edge case)'                 => [
                <<<'TXT'
                · ● Title-title-title-title-t ...   123.450 s DONE
                TXT,
                50,
                'Title-title-title-title-t',
                1,
                Mark::Task,
                null,
                [],
                Status::Done,
                123.45,
            ],
            'One character shorter (edge case)' => [
                <<<'TXT'
                · ● Title-title-title-title-  ...   123.450 s DONE
                TXT,
                50,
                'Title-title-title-title-',
                1,
                Mark::Task,
                null,
                [],
                Status::Done,
                123.45,
            ],
            'Value short'                       => [
                <<<'TXT'
                ● Title ............... value .W.   123.450 s DONE
                TXT,
                50,
                'Title',
                0,
                Mark::Task,
                'value',
                [Flag::Write],
                Status::Done,
                123.45,
            ],
            'Value long'                        => [
                <<<'TXT'
                ● Title title title title tit
                · le title title ...... value .W.   123.450 s DONE
                TXT,
                50,
                'Title title title title title title title',
                0,
                Mark::Task,
                'value',
                [Flag::Write],
                Status::Done,
                123.45,
            ],
            'Value almost too long'             => [
                <<<'TXT'
                ● Title title title title tit
                · le title title title! value .W.   123.450 s DONE
                TXT,
                50,
                'Title title title title title title title title!',
                0,
                Mark::Task,
                'value',
                [Flag::Write],
                Status::Done,
                123.45,
            ],
            'Value too long'                    => [
                <<<'TXT'
                ● Title title title title tit
                · le title title title!
                · ............... value value .W.   123.450 s DONE
                TXT,
                50,
                'Title title title title title title title title!',
                0,
                Mark::Task,
                'value value',
                [Flag::Write],
                Status::Done,
                123.45,
            ],
        ];
    }
    // </editor-fold>
}
