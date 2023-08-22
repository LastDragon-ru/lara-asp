<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

// @phpcs:disable Generic.Files.LineLength.TooLong

#[CoversClass(Preprocessor::class)]
class PreprocessorTest extends TestCase {
    public function testProcess(): void {
        $content     = <<<'MARKDOWN'
            Bla bla bla [test]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [test]: ./path/to/file

            [test]: ./path/to/file
            [//]: # (start: hash)

            outdated

            [//]: # (end: hash)
            MARKDOWN;
        $instruction = Mockery::mock(Instruction::class);
        $instruction
            ->shouldReceive('getName')
            ->once()
            ->andReturn('test');
        $instruction
            ->shouldReceive('process')
            ->with('path', './path/to/file')
            ->once()
            ->andReturn('content');

        $preprocessor = (new Preprocessor())
            ->addInstruction($instruction);

        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [test]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

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
