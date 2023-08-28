<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

// @phpcs:disable Generic.Files.LineLength.TooLong

#[CoversClass(Preprocessor::class)]
class PreprocessorTest extends TestCase {
    public function testProcess(): void {
        $content         = <<<'MARKDOWN'
            Bla bla bla [test]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [empty]: ./path/to/file

            [test]: ./path/to/file

            [test]: <./path/to/file>
            [//]: # (start: hash)

            [test]: ./path/to/file
            [//]: # (start: nested-hash)

            outdated

            [//]: # (end: nested-hash)

            [//]: # (end: hash)
            MARKDOWN;
        $testInstruction = Mockery::mock(Instruction::class);
        $testInstruction
            ->shouldReceive('getName')
            ->once()
            ->andReturn('test');
        $testInstruction
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
                return '';
            }
        };

        $preprocessor = (new Preprocessor())
            ->addInstruction($testInstruction)
            ->addInstruction($emptyInstruction);

        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [test]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [empty]: ./path/to/file
            [//]: # (start: 8c371cac4ef3bc60b4480789fd5c8dba395daff5ba46b9d4b0eebab34f1cdecf)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: 8c371cac4ef3bc60b4480789fd5c8dba395daff5ba46b9d4b0eebab34f1cdecf)

            [test]: ./path/to/file
            [//]: # (start: e4812e1f99f65a72f87b6abe2a385b620a824c2d6fb5a18d0704da017d3ab90a)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: e4812e1f99f65a72f87b6abe2a385b620a824c2d6fb5a18d0704da017d3ab90a)

            [test]: <./path/to/file>
            [//]: # (start: e4812e1f99f65a72f87b6abe2a385b620a824c2d6fb5a18d0704da017d3ab90a)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: e4812e1f99f65a72f87b6abe2a385b620a824c2d6fb5a18d0704da017d3ab90a)
            MARKDOWN,
            $preprocessor->process('path', $content),
        );
    }
}
