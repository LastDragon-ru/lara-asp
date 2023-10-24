<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Mockery;
use Mockery\Matcher\IsEqual;
use PHPUnit\Framework\Attributes\CoversClass;

// @phpcs:disable Generic.Files.LineLength.TooLong

#[CoversClass(Preprocessor::class)]
class PreprocessorTest extends TestCase {
    public function testProcess(): void {
        $content                    = <<<'MARKDOWN'
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

            [parameterizable]: ./path/to/file

            [parameterizable]: ./path/to/file

            [parameterizable]: ./path/to/file/parametrized ({"a": "aa", "b": "bb"})

            [parameterizable]: ./path/to/file/parametrized ({"a":"aa","b":"bb"})
            MARKDOWN;
        $parameterizableInstruction = Mockery::mock(ParameterizableInstruction::class);
        $parameterizableInstruction
            ->shouldReceive('getName')
            ->once()
            ->andReturn('parameterizable');
        $parameterizableInstruction
            ->shouldReceive('getParameters')
            ->times(4)
            ->andReturn(PreprocessorTest__Parameters::class);
        $parameterizableInstruction
            ->shouldReceive('process')
            ->with('path', './path/to/file', new IsEqual(new PreprocessorTest__Parameters()))
            ->once()
            ->andReturn('parameterizable()');
        $parameterizableInstruction
            ->shouldReceive('process')
            ->with('path', './path/to/file/parametrized', new IsEqual(new PreprocessorTest__Parameters('aa', 'bb')))
            ->once()
            ->andReturn('parameterizable(aa, bb)');

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

        $emptyInstruction = Mockery::mock(Instruction::class);
        $emptyInstruction
            ->shouldReceive('getName')
            ->once()
            ->andReturn('empty');

        $preprocessor = $this->app->make(Preprocessor::class)
            ->addInstruction($parameterizableInstruction)
            ->addInstruction($processableInstruction)
            ->addInstruction($emptyInstruction);

        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [empty]: ./path/to/file
            [//]: # (start: 8619b6a617a04e2b1ed8916cd29b8e9947a9157ffdef8d2e8a51fe60fbc13948)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: 8619b6a617a04e2b1ed8916cd29b8e9947a9157ffdef8d2e8a51fe60fbc13948)

            [processable]: ./path/to/file
            [//]: # (start: 378a07bc67ecbe88f5d2f642e70681461f61d3f2f5e83379015b879110c83947)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 378a07bc67ecbe88f5d2f642e70681461f61d3f2f5e83379015b879110c83947)

            [processable]: <./path/to/file>
            [//]: # (start: 378a07bc67ecbe88f5d2f642e70681461f61d3f2f5e83379015b879110c83947)
            [//]: # (warning: Generated automatically. Do not edit.)

            content

            [//]: # (end: 378a07bc67ecbe88f5d2f642e70681461f61d3f2f5e83379015b879110c83947)

            [parameterizable]: ./path/to/file
            [//]: # (start: 9067b51752787f145e0397d27c7efdc24efa41f0403b92649b3f2d76decdacd9)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable()

            [//]: # (end: 9067b51752787f145e0397d27c7efdc24efa41f0403b92649b3f2d76decdacd9)

            [parameterizable]: ./path/to/file
            [//]: # (start: 9067b51752787f145e0397d27c7efdc24efa41f0403b92649b3f2d76decdacd9)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable()

            [//]: # (end: 9067b51752787f145e0397d27c7efdc24efa41f0403b92649b3f2d76decdacd9)

            [parameterizable]: ./path/to/file/parametrized ({"a": "aa", "b": "bb"})
            [//]: # (start: 731f61b0b6cc1a6592f352c5fd1bf9219c75d5fc1826d01ca06b47758ffca736)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable(aa, bb)

            [//]: # (end: 731f61b0b6cc1a6592f352c5fd1bf9219c75d5fc1826d01ca06b47758ffca736)

            [parameterizable]: ./path/to/file/parametrized ({"a":"aa","b":"bb"})
            [//]: # (start: 731f61b0b6cc1a6592f352c5fd1bf9219c75d5fc1826d01ca06b47758ffca736)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable(aa, bb)

            [//]: # (end: 731f61b0b6cc1a6592f352c5fd1bf9219c75d5fc1826d01ca06b47758ffca736)
            MARKDOWN,
            $preprocessor->process('path', $content),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PreprocessorTest__Parameters implements Serializable {
    public function __construct(
        public string $a = 'a',
        public string $b = 'b',
    ) {
        // empty
    }
}
