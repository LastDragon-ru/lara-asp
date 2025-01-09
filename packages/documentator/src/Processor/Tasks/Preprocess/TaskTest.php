<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\MetadataStorage;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase {
    use WithProcessor;

    private const string MARKDOWN = <<<'MARKDOWN'
        Bla bla bla [processable]: ./path/to/file should be ignored.

        [unknown]: ./path/to/file (should not be parsed)

        [test:empty]: <./path/to/file "value">

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

        > Quote
        >
        > [test:instruction]: ./path/to/file

        [test:document]: file.md
        MARKDOWN;

    public function testParse(): void {
        $a    = new TaskTest__EmptyInstruction();
        $b    = new TaskTest__TestInstruction();
        $task = new class(
            $this->app()->make(ContainerResolver::class),
            $this->app()->make(Serializer::class),
        ) extends Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function parse(File $file, Document $document): array {
                return parent::parse($file, $document);
            }
        };

        $task->addInstruction($a::class);
        $task->addInstruction($b);

        $file     = Mockery::mock(File::class);
        $document = $this->app()->make(MarkdownContract::class)->parse(self::MARKDOWN);
        $tokens   = $task->parse($file, $document);
        $actual   = array_map(
            static function (array $tokens): array {
                return array_map(
                    static function (Token $token): array {
                        $nodes = array_map(LocationData::get(...), $token->nodes);

                        return [
                            $token->instruction,
                            $token->parameters,
                            $nodes,
                        ];
                    },
                    $tokens,
                );
            },
            $tokens,
        );

        self::assertEquals(
            [
                0           => [
                    'bb30809c6ca4c80a' => [
                        $b,
                        new TaskTest__Parameters('./path/to/file'),
                        [
                            new Location(7, 8, 0, null, 0),
                            new Location(9, 9, 0, null, 0),
                            new Location(21, 22, 0, null, 0),
                            new Location(23, 24, 0, null, 0),
                            new Location(31, 31, 0, null, 2),
                        ],
                    ],
                    'f5f55887ee415b3d' => [
                        $b,
                        new TaskTest__Parameters(
                            './path/to/file/parametrized',
                            'aa',
                            [
                                'a' => 'a',
                                'b' => 'b',
                            ],
                        ),
                        [
                            new Location(25, 26, 0, null, 0),
                            new Location(27, 28, 0, null, 0),
                        ],
                    ],
                ],
                PHP_INT_MAX => [
                    '4f76e5da6e5aabbc' => [
                        $a,
                        new TaskTest__ParametersEmpty('./path/to/file "value"'),
                        [
                            new Location(5, 6, 0, null, 0),
                        ],
                    ],
                ],
            ],
            $actual,
        );
    }

    public function testInvoke(): void {
        $task = $this->app()->make(Task::class)
            ->addInstruction(TaskTest__EmptyInstruction::class)
            ->addInstruction(TaskTest__TestInstruction::class)
            ->addInstruction(TaskTest__DocumentInstruction::class);

        $metadata = $this->app()->make(MetadataStorage::class);
        $path     = new FilePath('path/to/file.md');
        $file     = Mockery::mock(File::class, new WithProperties(), PropertiesMock::class);
        $file->makePartial();
        $file
            ->shouldUseProperty('path')
            ->value($path);
        $file
            ->shouldUseProperty('metadata')
            ->value($metadata);

        $metadata->set($file, Content::class, static::MARKDOWN);

        $actual     = '';
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('write')
            ->once()
            ->andReturnUsing(static function (mixed $path, string $content) use ($file, &$actual): File {
                $actual = $content;

                return $file;
            });

        $result = $this->getProcessorResult($filesystem, ($task)($file));

        self::assertTrue($result);
        self::assertEquals(
            <<<'MARKDOWN'
            Bla bla bla [processable]: ./path/to/file should be ignored.

            [unknown]: ./path/to/file (should not be parsed)

            [test:empty]: <./path/to/file "value">
            [//]: # (start: preprocess/4f76e5da6e5aabbc)
            [//]: # (warning: Generated automatically. Do not edit.)
            [//]: # (empty)
            [//]: # (end: preprocess/4f76e5da6e5aabbc)

            [test:instruction]: ./path/to/file
            [//]: # (start: preprocess/bb30809c6ca4c80a)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file","a":"a","b":[]})

            [//]: # (end: preprocess/bb30809c6ca4c80a)

            [test:instruction]: <./path/to/file>
            [//]: # (start: preprocess/bb30809c6ca4c80a)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file","a":"a","b":[]})

            [//]: # (end: preprocess/bb30809c6ca4c80a)

            [test:instruction]: ./path/to/file
            [//]: # (start: preprocess/bb30809c6ca4c80a)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file","a":"a","b":[]})

            [//]: # (end: preprocess/bb30809c6ca4c80a)

            [test:instruction]: ./path/to/file
            [//]: # (start: preprocess/bb30809c6ca4c80a)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file","a":"a","b":[]})

            [//]: # (end: preprocess/bb30809c6ca4c80a)

            [test:instruction]: ./path/to/file/parametrized ({"a": "aa", "b": {"a": "a", "b": "b"}})
            [//]: # (start: preprocess/f5f55887ee415b3d)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file\/parametrized","a":"aa","b":{"a":"a","b":"b"}})

            [//]: # (end: preprocess/f5f55887ee415b3d)

            [test:instruction]: ./path/to/file/parametrized ({"b":{ "b": "b","a": "a"},"a":"aa"})
            [//]: # (start: preprocess/f5f55887ee415b3d)
            [//]: # (warning: Generated automatically. Do not edit.)

            result({"target":".\/path\/to\/file\/parametrized","a":"aa","b":{"a":"a","b":"b"}})

            [//]: # (end: preprocess/f5f55887ee415b3d)

            > Quote
            >
            > [test:instruction]: ./path/to/file
            > [//]: # (start: preprocess/bb30809c6ca4c80a)
            > [//]: # (warning: Generated automatically. Do not edit.)
            >
            > result({"target":".\/path\/to\/file","a":"a","b":[]})
            >
            > [//]: # (end: preprocess/bb30809c6ca4c80a)
            >

            [test:document]: file.md
            [//]: # (start: preprocess/f895617206b7ff2f)
            [//]: # (warning: Generated automatically. Do not edit.)

            Summary [text](path/Document.md) summary [link][a282e9c32e7eee65-link] and summary[^a282e9c32e7eee65-1] and [self](#fragment) and [self][a282e9c32e7eee65-self].

            [a282e9c32e7eee65-link]: path/Document.md (title)
            [a282e9c32e7eee65-self]: #fragment

            [^a282e9c32e7eee65-1]: Footnote

            [//]: # (end: preprocess/f895617206b7ff2f)

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
 * @implements Instruction<TaskTest__ParametersEmpty>
 */
class TaskTest__EmptyInstruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:empty';
    }

    #[Override]
    public static function getPriority(): ?int {
        return PHP_INT_MAX;
    }

    #[Override]
    public static function getParameters(): string {
        return TaskTest__ParametersEmpty::class;
    }

    #[Override]
    public function __invoke(Context $context, Parameters $parameters): string {
        return '';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<TaskTest__Parameters>
 */
class TaskTest__TestInstruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return TaskTest__Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, Parameters $parameters): string {
        return 'result('.json_encode($parameters, JSON_THROW_ON_ERROR).')';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<TaskTest__ParametersEmpty>
 */
class TaskTest__DocumentInstruction implements Instruction {
    public function __construct(
        protected readonly MarkdownContract $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'test:document';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return TaskTest__ParametersEmpty::class;
    }

    #[Override]
    public function __invoke(Context $context, Parameters $parameters): Document {
        return $this->markdown->parse(
            <<<'MARKDOWN'
            Summary [text](../Document.md) summary [link][link] and summary[^1] and [self](#fragment) and [self][self].

            [link]: ../Document.md (title)
            [self]: #fragment

            [//]: # (start: block)
            [^1]: Footnote
            [//]: # (end: block)
            MARKDOWN,
            new FilePath('path/to/file.md'),
        );
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TaskTest__Parameters implements Parameters {
    /**
     * @param array<string, string> $b
     */
    public function __construct(
        public readonly string $target,
        public readonly string $a = 'a',
        public readonly array $b = [],
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class TaskTest__ParametersEmpty implements Parameters {
    public function __construct(
        public readonly string $target,
    ) {
        // empty
    }
}
