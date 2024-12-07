<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Editor\Editor;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_key_first;
use function array_values;

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
    public function testInvoke(string $expected, int $level, string $markdown): void {
        $document = new class($markdown) extends Document {
            #[Override]
            public function getNode(): DocumentNode {
                return parent::getNode();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getLines(): array {
                return parent::getLines();
            }
        };
        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $editor   = new Editor(array_values($lines), $offset);
        $actual   = (string) $editor->mutate((new Renumber($level))($document));

        self::assertEquals($expected, $actual);
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
                =========

                Foo *bar*
                ---------

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

                # Header 1

                Text text.

                ## Header 2

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
                =========

                Foo *bar*
                ---------
                MARKDOWN,
                1,
                <<<'MARKDOWN'
                ### Header 2

                ## Header 1

                Text text.

                ### Header 2

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
                =========

                Foo *bar*
                ---------
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
