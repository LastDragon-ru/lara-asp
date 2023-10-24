<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

// @phpcs:disable Generic.Files.LineLength.TooLong

#[CoversClass(Preprocessor::class)]
class PreprocessorTest extends TestCase {
    public function testProcess(): void {
        $content                = <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [empty]: ./path/to/file

            [processable]: ./path/to/file

            [processable]: <./path/to/file>
            [//]: # (start: hash)

            [processable]: ./path/to/file
            [//]: # (start: nested-hash)

            outdated

            [//]: # (end: nested-hash)

            [//]: # (end: hash)
            MARKDOWN;
        $processableInstruction = Mockery::mock(ProcessableInstruction::class);
        $processableInstruction
            ->shouldReceive('getName')
            ->once()
            ->andReturn('processable');
        $processableInstruction
            ->shouldReceive('process')
            ->with('path', './path/to/file')
            ->once()
            ->andReturn('content');
        $emptyInstruction = new class() implements Instruction {
            public static function getName(): string {
                return 'empty';
            }

            public static function getDescription(): string {
                return '';
            }

            public static function getTargetDescription(): ?string {
                return '';
            }

            public function process(string $path, string $target): string {
                return 'should be ignored';
            }
        };

        $preprocessor = (new Preprocessor())
            ->addInstruction($processableInstruction)
            ->addInstruction($emptyInstruction);

        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [empty]: ./path/to/file
            [//]: # (start: 8c371cac4ef3bc60b4480789fd5c8dba395daff5ba46b9d4b0eebab34f1cdecf)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: 8c371cac4ef3bc60b4480789fd5c8dba395daff5ba46b9d4b0eebab34f1cdecf)

            [processable]: ./path/to/file
            [//]: # (start: 15315c7cc8004bec2bb9b2707e8033d107007b6ca4b841181f6c17aef8f15bbc)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 15315c7cc8004bec2bb9b2707e8033d107007b6ca4b841181f6c17aef8f15bbc)

            [processable]: <./path/to/file>
            [//]: # (start: 15315c7cc8004bec2bb9b2707e8033d107007b6ca4b841181f6c17aef8f15bbc)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 15315c7cc8004bec2bb9b2707e8033d107007b6ca4b841181f6c17aef8f15bbc)
            MARKDOWN,
            $preprocessor->process('path', $content),
        );
    }
}
