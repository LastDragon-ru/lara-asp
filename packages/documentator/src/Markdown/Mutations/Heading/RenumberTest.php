<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Renumber::class)]
final class RenumberTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param int<1, 6> $level
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, int $level, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Renumber($level));

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, int<1, 6>, string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'same level' => [
                <<<'MARKDOWN'
                ## A

                ### B ###

                MARKDOWN,
                2,
                <<<'MARKDOWN'
                ## A

                ### B ###
                MARKDOWN,
            ],
            'increase'   => [
                <<<'MARKDOWN'
                ### Header 2

                * item
                * item

                ## Header 1

                Text text.

                ### Header 2

                * item

                Text text.

                #### Header 3
                ##### Header 4
                ###### Header 5
                ###### Header 6

                Text text.

                ## Special
                #### foo
                ### foo
                ## foo

                Foo *bar*
                -----

                ### Foo *bar*

                ### Header 2

                * item

                MARKDOWN,
                2,
                <<<'MARKDOWN'
                ## Header 2

                * item
                * item

                # Header 1

                Text text.

                ## Header 2 ##

                * item

                Text text.

                ### Header 3
                #### Header 4 ####
                ##### Header 5
                ###### Header 6

                Text text.

                # Special
                 ### foo
                  ## foo
                   # foo

                Foo *bar*
                =========

                Foo *bar*
                ---------

                ## Header 2

                * item
                MARKDOWN,
            ],
            'decrease'   => [
                <<<'MARKDOWN'
                ## Header 2

                * item
                * item

                # Header 1

                Text text.

                ## Header 2

                * item

                Text text.

                ### Header 3
                #### Header 4
                ##### Header 5
                ##### Header 6

                Text text.

                # Special
                ### foo
                ## foo
                # foo

                Foo *bar*
                =====

                ## Foo *bar*

                ## Header 2

                * item

                MARKDOWN,
                1,
                <<<'MARKDOWN'
                ### Header 2

                * item
                * item

                ## Header 1

                Text text.

                ### Header 2

                * item

                Text text.

                #### Header 3
                ##### Header 4
                ###### Header 5
                ###### Header 6

                Text text.

                ## Special
                #### foo
                 ### foo
                  ## foo

                Foo *bar*
                -----

                ### Foo *bar*

                ### Header 2

                * item
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
