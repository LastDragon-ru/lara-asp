<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

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
#[CoversClass(MakeSplittable::class)]
final class MakeSplittableTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Footnotes must be removed[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2].

            [^1]: footnote 1
            [^2]: footnote 2

            # References must be inlined

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            [link]: https://example.com
            [image]: https://example.com

            # Links to the self must be removed

            Text text [link](https://example.com) text text [`link`][link] text
            text text [self][self] text text [self](./#fragment).

            [self]: #fragment
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
        $mutation = new MakeSplittable();
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Footnotes must be removed

            Text text text text text  text text text  text text text
            text text text text text .

            # References must be inlined

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text ![image](https://example.com) text text.

            # Links to the self must be removed

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text self text text self.

            MARKDOWN,
            $actual,
        );
    }
}
