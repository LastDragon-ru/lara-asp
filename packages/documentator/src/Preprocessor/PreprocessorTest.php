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
            Bla bla bla [Link](./path/to/file "test") should be ignored.

            [Link](./path/to/file "unknown")

            [Link](./path/to/file "test")

            [Link](./path/to/file "test")<!-- start:hash -->

            outdated

            <!-- end:hash -->
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
            Bla bla bla [Link](./path/to/file "test") should be ignored.

            [Link](./path/to/file "unknown")

            [Link](./path/to/file "test")<!-- start:6a49bc25917851bb83f7f7b0c69ffea5774d4ccf Generated automatically. Do not edit. -->

            content

            <!-- end:6a49bc25917851bb83f7f7b0c69ffea5774d4ccf -->

            [Link](./path/to/file "test")<!-- start:6a49bc25917851bb83f7f7b0c69ffea5774d4ccf Generated automatically. Do not edit. -->

            content

            <!-- end:6a49bc25917851bb83f7f7b0c69ffea5774d4ccf -->
            MARKDOWN,
            $preprocessor->process('path', $content),
        );
    }
}
