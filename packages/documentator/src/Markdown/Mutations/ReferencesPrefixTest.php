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
#[CoversClass(ReferencesPrefix::class)]
final class ReferencesPrefixTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            ![image][image]

            [link]: https://example.com
            [image]: https://example.com

            # Special

            ## Inside Quote

            > ![image][link]

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][link] cell. | Cell `\|` \\| ![table][image] |
            | Cell                    | Cell cell [table][link].      |
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
        $node     = $document->getNode();
        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $mutation = new ReferencesPrefix('prefix');
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][prefix-link] text
            text text ![image][prefix-image] text text.

            ![image][prefix-image]

            [prefix-link]: https://example.com
            [prefix-image]: https://example.com

            # Special

            ## Inside Quote

            > ![image][prefix-link]

            ## Inside Table

            | Header                  |  [Header][prefix-link]               |
            |-------------------------|-------------------------------|
            | Cell [link][prefix-link] cell. | Cell `\|` \\| ![table][prefix-image] |
            | Cell                    | Cell cell [table][prefix-link].      |
            MARKDOWN,
            $actual,
        );
    }
}
