<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Editor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(FootnotesRemove::class)]
final class FootnotesRemoveTest extends TestCase {
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
        $node     = $document->getNode();
        $lines    = $document->getLines();
        $mutation = new FootnotesRemove();
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text text text text  text text text  text text text
            text text text text text  text text text [^3] text.

            Text text text.

            [^4]: footnote 4
            MARKDOWN,
            $actual,
        );
    }
}
