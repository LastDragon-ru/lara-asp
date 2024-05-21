<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Testing\Mockery\MockProperties;
use Mockery;
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
    private const MARKDOWN = <<<'MARKDOWN'
        Bla bla bla [processable]: ./path/to/file should be ignored.

        [unknown]: ./path/to/file (should not be parsed)

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

    public function testParse(): void {
        $a            = new PreprocessorTest__EmptyInstruction();
        $b            = new PreprocessorTest__TestInstruction();
        $root         = Mockery::mock(Directory::class);
        $directory    = Mockery::mock(Directory::class);
        $preprocessor = Mockery::mock(Preprocessor::class, MockProperties::class);
        $preprocessor->shouldAllowMockingProtectedMethods();
        $preprocessor->makePartial();
        $preprocessor
            ->shouldUseProperty('container')
            ->value(
                $this->app()->make(ContainerResolver::class),
            );
        $preprocessor
            ->shouldUseProperty('serializer')
            ->value(
                $this->app()->make(Serializer::class),
            );

        $preprocessor->addInstruction($a);
        $preprocessor->addInstruction($b);

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getContent')
            ->once()
            ->andReturn(self::MARKDOWN);

        $tokens = $preprocessor->parse($root, $directory, $file);

        self::assertEquals(
            new TokenList([
                '88d510d98112f651df2ae08444a402cd8b6516cf4c27ad6115dbb2c03fe9ec62' => new Token(
                    $a,
                    new PreprocessorTest__TargetResolverAsIs(),
                    new Context($root, $directory, $file, './path/to/file', null),
                    null,
                    [
                        '[test:empty]: ./path/to/file' => '[test:empty]: ./path/to/file',
                    ],
                ),
                '4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095' => new Token(
                    $b,
                    new PreprocessorTest__TargetResolverAsValue(),
                    new Context($root, $directory, $file, './path/to/file', null),
                    new PreprocessorTest__Parameters(),
                    [
                        // phpcs:disable Squiz.Arrays.ArrayDeclaration.DoubleArrowNotAligned
                        '[test:instruction]: ./path/to/file' => '[test:instruction]: ./path/to/file',
                        <<<'TXT'
                        [test:instruction]: <./path/to/file>
                        [//]: # (start: hash)

                        [test:instruction]: ./path/to/file
                        [//]: # (start: nested-hash)

                        outdated

                        [//]: # (end: nested-hash)

                        [//]: # (end: hash)
                        TXT
                                                             => '[test:instruction]: <./path/to/file>',
                        // phpcs:enable
                    ],
                ),
                'ebe11a5c6bf74b7f70eec0c6b14ad768e159a9699273d7f07824ef116b37dfd3' => new Token(
                    $b,
                    new PreprocessorTest__TargetResolverAsValue(),
                    new Context(
                        $root,
                        $directory,
                        $file,
                        './path/to/file/parametrized',
                        '{"a": "aa", "b": {"a": "a", "b": "b"}}',
                    ),
                    new PreprocessorTest__Parameters(
                        'aa',
                        [
                            'a' => 'a',
                            'b' => 'b',
                        ],
                    ),
                    [
                        '[test:instruction]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})' => ''
                            .'[test:instruction]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})',
                        '[test:instruction]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})'    => ''
                            .'[test:instruction]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})',
                    ],
                ),
            ]),
            $tokens,
        );
    }

    public function testRun(): void {
        $actual = null;
        $root   = Mockery::mock(Directory::class);
        $file   = Mockery::mock(File::class);
        $file
            ->shouldReceive('getContent')
            ->atLeast()
            ->once()
            ->andReturn(static::MARKDOWN);
        $file
            ->shouldReceive('setContent')
            ->once()
            ->andReturnUsing(
                static function (string $content) use ($file, &$actual): File {
                    $actual = $content;

                    return $file;
                },
            );

        $preprocessor = $this->app()->make(Preprocessor::class)
            ->addInstruction(new PreprocessorTest__EmptyInstruction())
            ->addInstruction(new PreprocessorTest__TestInstruction());

        self::assertTrue($preprocessor->run($root, $root, $file, []));
        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file (should not be parsed)

            [test:empty]: ./path/to/file
            [//]: # (start: 88d510d98112f651df2ae08444a402cd8b6516cf4c27ad6115dbb2c03fe9ec62)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: 88d510d98112f651df2ae08444a402cd8b6516cf4c27ad6115dbb2c03fe9ec62)

            [test:instruction]: ./path/to/file
            [//]: # (start: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)

            [test:instruction]: <./path/to/file>
            [//]: # (start: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)

            [test:instruction]: ./path/to/file
            [//]: # (start: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)

            [test:instruction]: ./path/to/file
            [//]: # (start: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)
            [//]: # (warning: Generated automatically. Do not edit.)

            result(./path/to/file/a, {"a":"a","b":[]})

            [//]: # (end: 4a9c0bb168ac831e7b45d8d7a78694c12ee0a3273de7562cdbc47cdb7f64e095)

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
            $actual,
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
