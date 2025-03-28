<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function mb_trim;

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

    /**
     * @param Mutation<covariant Node>|null $mutation
     */
    #[DataProvider('dataProviderMutate')]
    public function testMutate(string $expected, string $content, ?Mutation $mutation): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $mutated  = $document->mutate($mutation ?? []);
        $actual   = (string) $mutated;

        if (mb_trim($expected) !== mb_trim($content)) {
            self::assertNotSame($document, $mutated);
        }

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
     * @return array<string, array{string, string, ?Mutation<covariant Node>}>
     */
    public static function dataProviderMutate(): array {
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
                new Changeset([new Replace(new Append(), 'fsdfsdfsdf')]),
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
                new Changeset([new Replace(new Append(), 'fsdfsdfsdf')]),
            ],
            'Blank'                                      => [
                <<<'MARKDOWN'


                MARKDOWN,
                <<<'MARKDOWN'


                MARKDOWN,
                null,
            ],
        ];
    }
    //</editor-fold>
}
