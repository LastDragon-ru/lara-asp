<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Editor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_key_first;
use function array_values;

/**
 * @internal
 */
#[CoversClass(FootnotesPrefix::class)]
final class FootnotesPrefixTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Header[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2] text text text [^3] text[^bignote].

            [^1]: footnote 1

            Text text text[^2].

            [^2]: footnote 2

            [^4]: footnote 4

            [^bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

                Text text text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.
            MARKDOWN;
        $document = new class($markdown, new FilePath(__FILE__)) extends Document {
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
        $node     = $document->getNode();
        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $mutation = new FootnotesPrefix('prefix');
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header[^prefix-1]

            Text text text[^prefix-2] text text [^prefix-1] text text text [^prefix-2] text text text
            text text[^prefix-1] text text text [^prefix-2] text text text [^3] text[^prefix-bignote].

            [^prefix-1]: footnote 1

            Text text text[^prefix-2].

            [^prefix-2]: footnote 2

            [^4]: footnote 4

            [^prefix-bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

                Text text text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.
            MARKDOWN,
            $actual,
        );
    }
}
