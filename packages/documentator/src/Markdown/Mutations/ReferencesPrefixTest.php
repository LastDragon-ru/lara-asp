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
    public function testInvoke(): void {
        $document = new Document(
            <<<'MARKDOWN'
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
            MARKDOWN,
            'path/to/file.md',
        );
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
