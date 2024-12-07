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
#[CoversClass(MakeInlinable::class)]
final class MakeInlinableTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Footnotes must be prefixed[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2].

            [^1]: footnote 1
            [^2]: footnote 2

            # References must be prefixed

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            [link]: https://example.com
            [image]: https://example.com
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
        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $mutation = new MakeInlinable('prefix');
        $changes  = $mutation($document);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Footnotes must be prefixed[^prefix-1]

            Text text text[^prefix-2] text text [^prefix-1] text text text [^prefix-2] text text text
            text text[^prefix-1] text text text [^prefix-2].

            [^prefix-1]: footnote 1
            [^prefix-2]: footnote 2

            # References must be prefixed

            Text text [link](https://example.com) text text [`link`][prefix-link] text
            text text ![image][prefix-image] text text.

            [prefix-link]: https://example.com
            [prefix-image]: https://example.com
            MARKDOWN,
            $actual,
        );
    }
}
