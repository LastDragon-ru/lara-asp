<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Document::class)]
final class DocumentTest extends TestCase {
    // <editor-fold desc="Test">
    // =========================================================================
    #[DataProvider('dataProviderGetTitle')]
    public function testGetTitle(?string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = $document->getTitle();

        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderGetSummary')]
    public function testGetSummary(?string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = $document->getSummary();

        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderGetBody')]
    public function testGetBody(?string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = $document->getBody();

        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderIsEmpty')]
    public function testIsEmpty(bool $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = $document->isEmpty();

        self::assertSame($expected, $actual);
    }

    public function testMutate(): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse('');
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

    #[DataProvider('dataProviderToString')]
    public function testToString(string $expected, string $content, ?Mutation $mutation): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content)->mutate($mutation ?? new Nop());
        $actual   = (string) $document;

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string, string}>
     */
    public static function dataProviderGetTitle(): array {
        return [
            'No #'                => [
                null,
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ],
            'The # is not first'  => [
                null,
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ],
            'The # is empty'      => [
                null,
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Empty line before #' => [
                'Header',
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Comment before #'    => [
                'Header',
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ],
        ];
    }

    /**
     * @return array<string, array{?string, string}>
     */
    public static function dataProviderGetSummary(): array {
        return [
            'The # is not first'         => [
                null,
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ],
            'Summary is the first node'  => [
                'fsdfsdfsdf',
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ],
            'Quote before #'             => [
                null,
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Empty #'                    => [
                'fsdfsdfsdf',
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Multiline'                  => [
                <<<'TEXT'
                fsdfsdfsdf
                fsdfsdfsdf
                TEXT,
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ],
            'Comments should be ignored' => [
                <<<'TEXT'
                fsdfsdfsdf
                fsdfsdfsdf
                TEXT,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ],
        ];
    }

    /**
     * @return array<string, array{?string, string}>
     */
    public static function dataProviderGetBody(): array {
        return [
            'The # is not first'         => [
                null,
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ],
            'Summary is the first node'  => [
                <<<'TEXT'
                # Header

                sdfsdfsdf
                TEXT,
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ],
            'Quote before #'             => [
                null,
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf

                text text text
                MARKDOWN,
            ],
            'Empty #'                    => [
                <<<'TEXT'
                text text text

                text text text
                TEXT,
                <<<'MARKDOWN'
                #

                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ],
            'Multiline summary'          => [
                <<<'TEXT'
                text text text

                text text text
                TEXT,
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ],
            'Comments should be ignored' => [
                <<<'TEXT'
                <!-- Comment -->

                text text text
                TEXT,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                summary

                <!-- Comment -->

                text text text
                MARKDOWN,
            ],
        ];
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public static function dataProviderIsEmpty(): array {
        return [
            'Empty'          => [
                true,
                <<<'MARKDOWN'



                MARKDOWN,
            ],
            'Not empty'      => [
                false,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ],
            'Reference only' => [
                false,
                <<<'MARKDOWN'
                [unused]: ../path/to/file
                MARKDOWN,
            ],
            'Comment only'   => [
                false,
                <<<'MARKDOWN'
                <!-- comment -->
                MARKDOWN,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, ?Mutation}>
     */
    public static function dataProviderToString(): array {
        return [
            'Blank line on the end'                      => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                null,
            ],
            'No blank line on the end'                   => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
                null,
            ],
            'Blank line on the end + Empty Mutation'     => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                new Changeset([]),
            ],
            'No blank line on the end + Empty Mutation'  => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
                new Changeset([]),
            ],
            'Blank line on the end + Append Mutation'    => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                new Changeset([[new Append(), 'fsdfsdfsdf']]),
            ],
            'No blank line on the end + Append Mutation' => [
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
                new Changeset([[new Append(), 'fsdfsdfsdf']]),
            ],
        ];
    }
    //</editor-fold>
}
