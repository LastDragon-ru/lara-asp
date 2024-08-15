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
#[CoversClass(ReferencesPrefix::class)]
final class ReferencesPrefixTest extends TestCase {
    private const Markdown = <<<'MARKDOWN'
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

    public function testInvoke(): void {
        $document = new Document(self::Markdown, 'path/to/file.md');
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
        $mutation = new ReferencesPrefix();
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][a282e9c32e7eee65-link] text
            text text ![image][a282e9c32e7eee65-image] text text.

            ![image][a282e9c32e7eee65-image]

            [a282e9c32e7eee65-link]: https://example.com
            [a282e9c32e7eee65-image]: https://example.com

            # Special

            ## Inside Quote

            > ![image][a282e9c32e7eee65-link]

            ## Inside Table

            | Header                  |  [Header][a282e9c32e7eee65-link]               |
            |-------------------------|-------------------------------|
            | Cell [link][a282e9c32e7eee65-link] cell. | Cell `\|` \\| ![table][a282e9c32e7eee65-image] |
            | Cell                    | Cell cell [table][a282e9c32e7eee65-link].      |
            MARKDOWN,
            $actual,
        );
    }

    public function testInvokeExplicit(): void {
        $document = new Document(self::Markdown, 'path/to/file.md');
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
        $mutation = new ReferencesPrefix('prefix');
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

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
