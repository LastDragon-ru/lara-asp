<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated;

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
#[CoversClass(Unwrap::class)]
final class UnwrapTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
            # Header

            [//]: # (start: block)

            Text text text text text text text text text text text text text
            text text text text text text text text text text text text text
            text text text text text text text text text text text text.

            [//]: # (start: nested)

            Nested should be ignored.

            [//]: # (end: nested)

            [//]: # (end: block)

            > [//]: # (start: quote)
            > should work
            > [//]: # (end: quote)

            [//]: # (start: block)

            > Text text text.
            >
            > [//]: # (start: nested)
            > Nested should be ignored.
            > [//]: # (end: nested)

            [//]: # (end: block)

            [//]: # (start: without end)
            [//]: # (warning: Generated automatically. Do not edit.)

            Up to the end.
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
        $mutation = new Unwrap();
        $changes  = $mutation($document);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text text text text text text text text text text text text
            text text text text text text text text text text text text text
            text text text text text text text text text text text text.

            [//]: # (start: nested)

            Nested should be ignored.

            [//]: # (end: nested)

            > should work

            > Text text text.
            >
            > [//]: # (start: nested)
            > Nested should be ignored.
            > [//]: # (end: nested)

            Up to the end.
            MARKDOWN,
            $actual,
        );
    }
}
