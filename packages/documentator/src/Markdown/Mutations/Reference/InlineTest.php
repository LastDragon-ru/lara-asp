<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Editor\Editor;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_key_first;
use function array_values;

/**
 * @internal
 */
#[CoversClass(Inline::class)]
final class InlineTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            ![image][image]

            [link]: https://example.com
            [image]: https://example.com (image)
            [table]: https://example.com (table | cell)

            # Special

            ## Inside Quote

            > ![image][link]

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][link] cell. | Cell `\|` \\| ![table][table] |
            | Cell                    | Cell cell ![table][link].     |
            MARKDOWN;
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
        $mutation = new Inline();
        $changes  = $mutation($document);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text ![image](https://example.com "image") text text.

            ![image](https://example.com "image")

            # Special

            ## Inside Quote

            > ![image](https://example.com)

            ## Inside Table

            | Header                  |  [Header](https://example.com)               |
            |-------------------------|-------------------------------|
            | Cell [link](https://example.com) cell. | Cell `\|` \\| ![table](https://example.com "table \| cell") |
            | Cell                    | Cell cell ![table](https://example.com).     |
            MARKDOWN,
            $actual,
        );
    }
}
