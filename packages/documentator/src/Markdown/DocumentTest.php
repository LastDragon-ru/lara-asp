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
    #[DataProvider('dataProviderIsEmpty')]
    public function testIsEmpty(bool $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = $document->isEmpty();

        self::assertSame($expected, $actual);
    }

    public function testMutateMutation(): void {
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
            'Blank'                                      => [
                '',
                <<<'MARKDOWN'




                MARKDOWN,
                null,
            ],
        ];
    }
    //</editor-fold>
}
