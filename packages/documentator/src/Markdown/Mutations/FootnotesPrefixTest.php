<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Editor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

/**
 * @internal
 */
#[CoversClass(FootnotesPrefix::class)]
final class FootnotesPrefixTest extends TestCase {
    private const Markdown = <<<'MARKDOWN'
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

    public function testInvoke(): void {
        $document = new Document(self::Markdown, 'path/to/file.md');
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
        $mutation = new FootnotesPrefix();
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header[^a282e9c32e7eee65-1]

            Text text text[^a282e9c32e7eee65-2] text text [^a282e9c32e7eee65-1] text text text [^a282e9c32e7eee65-2] text text text
            text text[^a282e9c32e7eee65-1] text text text [^a282e9c32e7eee65-2] text text text [^3] text[^a282e9c32e7eee65-bignote].

            [^a282e9c32e7eee65-1]: footnote 1

            Text text text[^a282e9c32e7eee65-2].

            [^a282e9c32e7eee65-2]: footnote 2

            [^4]: footnote 4

            [^a282e9c32e7eee65-bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

                Text text text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.
            MARKDOWN,
            $actual,
        );
    }

    public function testInvokeExplicit(): void {
        $document = new Document(self::Markdown, __FILE__);
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
        $mutation = new FootnotesPrefix('prefix');
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

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
