<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines as LinesData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List\Mutagens;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading as HeadingNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Mutator::class)]
final class MutatorTest extends TestCase {
    public function testMutate(): void {
        $markdown  = $this->app()->make(Markdown::class);
        $document  = $markdown->parse(
            <<<'MARKDOWN'
            # Header A

            Text text text [link](https://example.com) text text text
            text text text [link](https://example.com) text text text
            text.

            # Header B

            Text text text [link](https://example.com) text text text
            text text text [link](https://example.com) text text text
            text.
            MARKDOWN,
        );
        $aMutation = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [
                    DocumentNode::class,
                ];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [
                    new Finalize(static function (Document $document): void {
                        $document->path = new FilePath(__FILE__);
                    }),
                    new Extract(new Location(1, 6)),
                    new Extract(new Location(9, 9)),
                    new Extract(new Location(11, 11)),
                ];
            }
        };
        $bMutation = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [
                    HeadingNode::class,
                    LinkNode::class,
                ];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [
                    new Delete(LocationData::get($node)),
                ];
            }
        };
        $mutator   = new Mutator([$aMutation, $bMutation]);
        $actual    = $mutator->mutate($markdown, $document, LinesData::get($document->node));

        self::assertSame(
            <<<'MARKDOWN'
            Text text text  text text text
            text text text  text text text
            text.

            Text text text  text text text

            text.

            MARKDOWN,
            (string) $actual,
        );
        self::assertEquals(
            new FilePath(__FILE__),
            $actual->path,
        );
    }

    public function testGetNodeMutagens(): void {
        $markdown  = $this->app()->make(Markdown::class);
        $document  = $markdown->parse(
            <<<'MARKDOWN'
            # Header A

            Text text text [link](https://example.com) text text text
            text text text [link](https://example.com) text text text
            text.

            # Header B

            Text text text [link](https://example.com) text text text
            text text text [link](https://example.com) text text text
            text.
            MARKDOWN,
        );
        $aMutation = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [
                    DocumentNode::class,
                ];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [
                    new Finalize(static function (): void {
                        // empty
                    }),
                    new Extract(new Location(1, 6)),
                    new Extract(new Location(9, 9)),
                ];
            }
        };
        $bMutation = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [
                    HeadingNode::class,
                    LinkNode::class,
                ];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [
                    new Delete(LocationData::get($node)),
                ];
            }
        };
        $mutator   = new class([$aMutation, $bMutation]) extends Mutator {
            #[Override]
            public function getNodeMutagens(Mutagens $mutagens, Document $document, Node $node): Mutagens {
                return parent::getNodeMutagens($mutagens, $document, $node);
            }
        };
        $mutagens  = $mutator->getNodeMutagens(new Mutagens(), $document, $document->node);
        $actual    = [
            'changes'    => [],
            'finalizers' => $mutagens->getFinalizers(),
        ];

        foreach ($mutagens->getChanges() as $location => $changes) {
            $actual['changes'][] = [$location, iterator_to_array($changes, false)];
        }

        self::assertEquals(
            [
                'changes'    => [
                    [
                        new Location(1, 6),
                        [
                            [new Location(0, 0), null],
                            [new Location(2, 2, 15, 27), null],
                            [new Location(3, 3, 15, 27), null],
                        ],
                    ],
                    [
                        new Location(9, 9),
                        [
                            [new Location(0, 0, 15, 27), null],
                        ],
                    ],
                ],
                'finalizers' => [
                    new Finalize(static function (): void {
                        // empty
                    }),
                ],
            ],
            $actual,
        );
    }

    public function testGetNodeMutations(): void {
        $a       = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [AbstractBlock::class];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [];
            }
        };
        $b       = new class() implements Mutation {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function nodes(): array {
                return [Paragraph::class];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function mutagens(Document $document, Node $node): array {
                return [];
            }
        };
        $mutator = new class([$b, $a]) extends Mutator {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getNodeMutations(Node $node): array {
                return parent::getNodeMutations($node);
            }
        };

        self::assertEquals([], $mutator->getNodeMutations(Mockery::mock(Node::class)));
        self::assertEquals([$a], $mutator->getNodeMutations(Mockery::mock(AbstractBlock::class)));
        self::assertEquals([$b, $a], $mutator->getNodeMutations(Mockery::mock(Paragraph::class)));
    }
}
