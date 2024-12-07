<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

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
#[CoversClass(RemoveToSelf::class)]
final class RemoveToSelfTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text [self][self-a] text text [self](./file.md#fragment) text text text
            text text [self][self-b] text text [self](./#fragment) text text text
            text text [self][self-c] text text [self](#fragment) text text text
            text text [self][self-d] text text [self](./file.md) text text text
            text text ![image][image] text text ![image](#fragment).

            [self-a]: ./file.md#fragment
            [self-b]: ./#fragment
            [self-c]: #fragment
            [self-d]: ./file.md
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text [self][self-a] text text [self](./file.md#fragment) text text text
            > text text [self][self-b] text text [self](./#fragment) text text text
            > text text [self][self-c] text text [self](#fragment) text text text
            > text text [self][self-d] text text [self](./file.md) text text text

            ## Inside Table

            | Header                    |  [Header][link]               |
            |---------------------------|-------------------------------|
            | Cell [link][self-a] cell. | Cell `\|` \\| ![table][image] |
            | Cell                      | Cell cell [table][self-a].    |
            MARKDOWN;
        $document = new class($markdown, new FilePath('path/to/file.md')) extends Document {
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
        $mutation = new RemoveToSelf();
        $changes  = $mutation($document);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text self text text self text text text
            text text [self][self-b] text text [self](./#fragment) text text text
            text text self text text self text text text
            text text self text text self text text text
            text text ![image][image] text text ![image](#fragment).

            [self-a]: ./file.md#fragment
            [self-b]: ./#fragment
            [self-c]: #fragment
            [self-d]: ./file.md
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text self text text self text text text
            > text text [self][self-b] text text [self](./#fragment) text text text
            > text text self text text self text text text
            > text text self text text self text text text

            ## Inside Table

            | Header                    |  [Header][link]               |
            |---------------------------|-------------------------------|
            | Cell link cell. | Cell `\|` \\| ![table][image] |
            | Cell                      | Cell cell table.    |
            MARKDOWN,
            $actual,
        );
    }
}
