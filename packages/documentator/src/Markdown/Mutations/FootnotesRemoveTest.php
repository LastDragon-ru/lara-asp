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
#[CoversClass(FootnotesRemove::class)]
final class FootnotesRemoveTest extends TestCase {
    public function testInvoke(): void {
        $document = new Document(
            <<<'MARKDOWN'
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
            MARKDOWN,
        );
        $node     = Cast::to(DocumentNode::class, (new ReflectionProperty($document, 'node'))->getValue($document));
        $lines    = Data::get($node, Lines::class) ?? [];
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
