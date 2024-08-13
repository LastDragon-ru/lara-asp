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
#[CoversClass(ReferencesInline::class)]
final class ReferencesInlineTest extends TestCase {
    public function testInvoke(): void {
        $document = new Document(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [link][link] text
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
            MARKDOWN,
        );
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
        $mutation = new ReferencesInline();
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor($lines))->mutate($changes);

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [link](https://example.com) text
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
