<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Document::class)]
final class DocumentTest extends TestCase {
    public function testGetTitle(): void {
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new Document(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new Document(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                MARKDOWN,
                new FilePath('path/to/FileName.txt'),
            ))
                ->getTitle(),
        );
    }

    public function testGetSummary(): void {
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            'fsdfsdfsdf',
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            'fsdfsdfsdf',
            (new Document(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new Document(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new Document(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
    }

    public function testGetBody(): void {
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getBody(),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            sdfsdfsdf
            MARKDOWN,
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getBody(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf

                text text text
                MARKDOWN,
            ))
                ->getBody(),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            text text text

            text text text
            MARKDOWN,
            (new Document(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ))
                ->getBody(),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            text text text

            text text text
            MARKDOWN,
            (new Document(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ))
                ->getBody(),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            <!-- Comment -->

            text text text
            MARKDOWN,
            (new Document(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                text text text

                <!-- Comment -->

                text text text
                MARKDOWN,
            ))
                ->getBody(),
        );
    }

    public function testIsEmpty(): void {
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                [unused]: ../path/to/file
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                <!-- comment -->
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertTrue(
            (new Document(
                <<<'MARKDOWN'



                MARKDOWN,
            ))
                ->isEmpty(),
        );
    }

    public function testMutate(): void {
        $document = new Document('');
        $mutation = Mockery::mock(Mutation::class);
        $mutation
            ->shouldReceive('__invoke')
            ->with(Mockery::type(Document::class))
            ->once()
            ->andReturn([
                // empty
            ]);

        $clone   = clone $document;
        $mutated = $document->mutate($mutation);

        self::assertNotSame($document, $mutated);
        self::assertEquals($clone, $document);
    }

    public function testToString(): void {
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf

            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
            )),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf
            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            )),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf

            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
            ))
                ->mutate(new Changeset([])),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf

            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->mutate(new Changeset([])),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf
            fsdfsdfsdf

            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
            ))
                ->mutate(new Changeset([[new Append(), 'fsdfsdfsdf']])),
        );
        self::assertEquals(
            <<<'MARKDOWN'
            fsdfsdfsdf
            fsdfsdfsdf
            fsdfsdfsdf

            MARKDOWN,
            (string) (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->mutate(new Changeset([[new Append(), 'fsdfsdfsdf']])),
        );
    }
}
