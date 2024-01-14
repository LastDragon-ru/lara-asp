<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Mockery;
use Mockery\Matcher\IsEqual;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Preprocessor::class)]
final class PreprocessorTest extends TestCase {
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

            [parameterizable]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})

            [parameterizable]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})
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
            ->with(
                'path',
                './path/to/file/parametrized',
                new IsEqual(
                    new PreprocessorTest__Parameters('aa', ['a' => 'a', 'b' => 'b']),
                ),
            )
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

        $preprocessor = Container::getInstance()->make(Preprocessor::class)
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
            [//]: # (start: 5d070af95e5691621d63e901616cc0fa03d3253c24d1dcf27023d8a1ce01d9fb)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable()

            [//]: # (end: 5d070af95e5691621d63e901616cc0fa03d3253c24d1dcf27023d8a1ce01d9fb)

            [parameterizable]: ./path/to/file
            [//]: # (start: 5d070af95e5691621d63e901616cc0fa03d3253c24d1dcf27023d8a1ce01d9fb)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable()

            [//]: # (end: 5d070af95e5691621d63e901616cc0fa03d3253c24d1dcf27023d8a1ce01d9fb)

            [parameterizable]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})
            [//]: # (start: a91869c99598538f0f98ed293004603fd2c24d938c8c6d5a8e7267ba56aeb86e)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable(aa, bb)

            [//]: # (end: a91869c99598538f0f98ed293004603fd2c24d938c8c6d5a8e7267ba56aeb86e)

            [parameterizable]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})
            [//]: # (start: a91869c99598538f0f98ed293004603fd2c24d938c8c6d5a8e7267ba56aeb86e)
            [//]: # (warning: Generated automatically. Do not edit.)

            parameterizable(aa, bb)

            [//]: # (end: a91869c99598538f0f98ed293004603fd2c24d938c8c6d5a8e7267ba56aeb86e)
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
    /**
     * @param array<string, string> $b
     */
    public function __construct(
        public string $a = 'a',
        public array $b = [],
    ) {
        // empty
    }
}
