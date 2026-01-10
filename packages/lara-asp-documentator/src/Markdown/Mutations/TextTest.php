<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Text::class)]
final class TextTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            12345 12345 12345 12345 12345
            12345 12345 12345 12345 12345
            12345 12345 12345 12345 12345
            12345 12345 12345 12345 12345
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Text(new Location(3, 4, 5, 24)));

        self::assertSame(
            " 12345 12345 12345 12345\n12345 12345 12345 12345 \n",
            $actual,
        );
    }
}
