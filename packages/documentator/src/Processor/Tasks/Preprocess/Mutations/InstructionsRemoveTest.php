<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(InstructionsRemove::class)]
final class InstructionsRemoveTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            [test:instruction]: /path/to/file.md
            [//]: # (start: block)

            Text text text text text text text text text text text text text

            [//]: # (end: block)

            > [test:instruction]: /path/to/file.md
            > [//]: # (start: quote)
            > should work
            > [//]: # (end: quote)

            [//]: # (start: block)
            [test:instruction]: /path/to/file.md
            [//]: # (end: block)

            [test]: /path/to/file.md
            MARKDOWN;

        $instructions = Mockery::mock(Instructions::class);
        $instructions
            ->shouldReceive('has')
            ->with('test:instruction')
            ->twice()
            ->andReturn(true);
        $instructions
            ->shouldReceive('has')
            ->atLeast()
            ->once()
            ->andReturn(false);

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new InstructionsRemove($instructions));

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            [//]: # (start: block)

            Text text text text text text text text text text text text text

            [//]: # (end: block)

            > [//]: # (start: quote)
            > should work
            > [//]: # (end: quote)

            [//]: # (start: block)
            [test:instruction]: /path/to/file.md
            [//]: # (end: block)

            [test]: /path/to/file.md

            MARKDOWN,
            $actual,
        );
    }
}
