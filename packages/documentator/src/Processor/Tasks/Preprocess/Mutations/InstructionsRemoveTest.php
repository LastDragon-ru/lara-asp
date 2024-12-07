<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Editor;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceList;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_key_first;
use function array_values;

/**
 * @internal
 */
#[CoversClass(InstructionsRemove::class)]
final class InstructionsRemoveTest extends TestCase {
    public function testInvoke(): void {
        $markdown = <<<'MARKDOWN'
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

        $instructions = Mockery::mock(InstanceList::class);
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

        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $mutation = new InstructionsRemove($instructions);
        $changes  = $mutation($document);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals(
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
