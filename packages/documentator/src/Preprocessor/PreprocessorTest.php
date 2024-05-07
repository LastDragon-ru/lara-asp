<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Preprocessor::class)]
final class PreprocessorTest extends TestCase {
    public function testProcess(): void {
        $content = <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [test:empty]: ./path/to/file

            [test:instruction]: ./path/to/file

            [test:instruction]: <./path/to/file>
            [//]: # (start: hash)

            [test:instruction]: ./path/to/file
            [//]: # (start: nested-hash)

            outdated

            [//]: # (end: nested-hash)

            [//]: # (end: hash)

            [test:instruction]: ./path/to/file

            [test:instruction]: ./path/to/file

            [test:instruction]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})

            [test:instruction]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})
            MARKDOWN;

        $preprocessor = $this->app()->make(Preprocessor::class)
            ->addInstruction(new PreprocessorTest__EmptyInstruction())
            ->addInstruction(new PreprocessorTest__TestInstruction());

        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file

            [test:empty]: ./path/to/file
            [//]: # (start: caf14319a44edf638bf2ba4b4c76caab5a3d85ee06d5c86387fbdb703c8b5c84)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: caf14319a44edf638bf2ba4b4c76caab5a3d85ee06d5c86387fbdb703c8b5c84)

            [test:instruction]: ./path/to/file
            [//]: # (start: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)

            [test:instruction]: <./path/to/file>
            [//]: # (start: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)

            [test:instruction]: ./path/to/file
            [//]: # (start: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)

            [test:instruction]: ./path/to/file
            [//]: # (start: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 15a77bf03e0261f0d7d2af698861fc23733fb4f09e0570e3b2570f4fe7b2694c)

            [test:instruction]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})
            [//]: # (start: ebe11a5c6bf74b7f70eec0c6b14ad768e159a9699273d7f07824ef116b37dfd3)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/parametrized/aa, {"a":"aa","b":{"a":"a","b":"b"}})

            [//]: # (end: ebe11a5c6bf74b7f70eec0c6b14ad768e159a9699273d7f07824ef116b37dfd3)

            [test:instruction]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})
            [//]: # (start: ebe11a5c6bf74b7f70eec0c6b14ad768e159a9699273d7f07824ef116b37dfd3)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/parametrized/aa, {"a":"aa","b":{"a":"a","b":"b"}})

            [//]: # (end: ebe11a5c6bf74b7f70eec0c6b14ad768e159a9699273d7f07824ef116b37dfd3)
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
 *
 * @implements Instruction<string, null>
 */
class PreprocessorTest__EmptyInstruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:empty';
    }

    #[Override]
    public static function getResolver(): string {
        return PreprocessorTest__TargetResolverAsIs::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return '';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<PreprocessorTest__Value, PreprocessorTest__Parameters>
 */
class PreprocessorTest__TestInstruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction';
    }

    #[Override]
    public static function getResolver(): string {
        return PreprocessorTest__TargetResolverAsValue::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return PreprocessorTest__Parameters::class;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return sprintf('result(%s, %s)', $target->value, json_encode($parameters, JSON_THROW_ON_ERROR));
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PreprocessorTest__Value {
    public function __construct(
        public string $value,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Resolver<null, string>
 */
class PreprocessorTest__TargetResolverAsIs implements Resolver {
    #[Override]
    public function resolve(Context $context, mixed $parameters): mixed {
        return $context->target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Resolver<PreprocessorTest__Parameters, PreprocessorTest__Value>
 */
class PreprocessorTest__TargetResolverAsValue implements Resolver {
    #[Override]
    public function resolve(Context $context, mixed $parameters): mixed {
        return new PreprocessorTest__Value("{$context->target}/{$parameters->a}");
    }
}

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
