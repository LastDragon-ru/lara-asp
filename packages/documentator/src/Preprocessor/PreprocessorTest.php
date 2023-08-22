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

            [test]: ./path/to/file
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
            [//]: # (start: a3fab3c67a30d7c1ba21b17c4c0e9e609e78373c)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: a3fab3c67a30d7c1ba21b17c4c0e9e609e78373c)

            [test]: ./path/to/file
            [//]: # (start: 8c3f20586897a62ee759aae56b703dd6cd11a8ad)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 8c3f20586897a62ee759aae56b703dd6cd11a8ad)

            [test]: ./path/to/file
            [//]: # (start: 8c3f20586897a62ee759aae56b703dd6cd11a8ad)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 8c3f20586897a62ee759aae56b703dd6cd11a8ad)
            MARKDOWN,
            $preprocessor->process('path', $content),
        );
    }
}
