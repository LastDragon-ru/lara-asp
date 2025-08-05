<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AutolinkExpand::class)]
#[CoversClass(Base::class)]
final class AutolinkExpandTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text <https://example.com> text text text text
            text <https://example.com/ /path/to/something> text
            text text <example@example.com>.

            ## Inside Table

            | Header A                                 | Header B                                |
            |------------------------------------------|-----------------------------------------|
            | Cell <https://example.com/\|/path> cell. | <example@example.com>                   |
            | Cell                                     | <https://example.com/%20/path/in/cell>. |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new AutolinkExpand());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text [https://example.com](https://example.com) text text text text
            text <https://example.com/ /path/to/something> text
            text text [example@example.com](mailto:example@example.com).

            ## Inside Table

            | Header A                                 | Header B                                |
            |------------------------------------------|-----------------------------------------|
            | Cell [https://example.com/\|/path](https://example.com/%7C/path) cell. | [example@example.com](mailto:example@example.com)                   |
            | Cell                                     | [https://example.com/ /path/in/cell](<https://example.com/ /path/in/cell>). |

            MARKDOWN,
            $actual,
        );
    }
}
