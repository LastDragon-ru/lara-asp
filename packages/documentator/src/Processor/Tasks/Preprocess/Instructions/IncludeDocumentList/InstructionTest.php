<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use League\CommonMark\Node\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $path, string $content): void {
        // Prepare
        $path        = (new FilePath(self::getTestData()->path($path)))->getNormalizedPath();
        $root        = new Directory($path->getDirectoryPath(), false);
        $file        = new File($path, false);
        $document    = new Document($content, $path);
        $instruction = (new Query())->where(Query::type(Block::class))->findOne($document->getNode());

        self::assertInstanceOf(Block::class, $instruction);

        // Parameters
        $target               = $instruction->getDestination();
        $parameters           = $instruction->getTitle();
        $parameters           = $parameters !== ''
            ? (array) json_decode($parameters, true, flags: JSON_THROW_ON_ERROR)
            : [];
        $parameters['target'] = $target;
        $parameters           = json_encode($parameters, JSON_THROW_ON_ERROR);
        $parameters           = $this->app()->make(Serializer::class)->deserialize(Parameters::class, $parameters);

        // Test
        $context  = new Context($root, $file, $document, $instruction, new Nop());
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $parameters);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>
    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'Same Directory / Default parameters'    => [
                <<<'MARKDOWN'
                # [`<` Document B `>`](<Document B.md>)

                Summary text.

                [Read more](<Document B.md>).

                # [Document A](<Document A.md>)

                Summary text with special characters `<`, `>`, `&`.

                [Read more](<Document A.md>).

                MARKDOWN,
                'Document.md',
                <<<'MARKDOWN'
                [include:document-list]: ./
                MARKDOWN,
            ],
            'Another Directory / Default parameters' => [
                <<<'MARKDOWN'
                # [`<` Document B `>`](<InstructionTest/Document B.md>)

                Summary text.

                [Read more](<InstructionTest/Document B.md>).

                # [Document](<InstructionTest/Document.md>)

                Document summary.

                [Read more](<InstructionTest/Document.md>).

                # [Document A](<InstructionTest/Document A.md>)

                Summary text with special characters `<`, `>`, `&`.

                [Read more](<InstructionTest/Document A.md>).

                MARKDOWN,
                '.php',
                <<<'MARKDOWN'
                [include:document-list]: ./InstructionTest
                MARKDOWN,
            ],
            'Nested Directories'                     => [
                <<<'MARKDOWN'
                # [Nested B](<B/Document B.md>)

                Summary [text](../Document.md).

                [Read more](<B/Document B.md>).

                # [Nested A](<A/Document A.md>)

                Summary [text](../Document.md).

                [Read more](<A/Document A.md>).

                # [Document C](<Document C.md>)

                Summary [text](../Document.md) summary [link](../Document.md "title") and summary and self and self.

                [Read more](<Document C.md>).

                MARKDOWN,
                'nested/Document.md',
                <<<'MARKDOWN'
                [include:document-list]: . ({"depth": null, "order": "Desc"})
                MARKDOWN,
            ],
            'Level `null`'                           => [
                <<<'MARKDOWN'
                ## [`<` Document B `>`](<Document B.md>)

                Summary text.

                [Read more](<Document B.md>).

                ## [Document A](<Document A.md>)

                Summary text with special characters `<`, `>`, `&`.

                [Read more](<Document A.md>).

                MARKDOWN,
                'Document.md',
                <<<'MARKDOWN'
                # Header

                Text text.

                [include:document-list]: ./ ({"level": null})
                MARKDOWN,
            ],
            'Level `0`'                              => [
                <<<'MARKDOWN'
                ### [`<` Document B `>`](<Document B.md>)

                Summary text.

                [Read more](<Document B.md>).

                ### [Document A](<Document A.md>)

                Summary text with special characters `<`, `>`, `&`.

                [Read more](<Document A.md>).

                MARKDOWN,
                'Document.md',
                <<<'MARKDOWN'
                ### Header

                Text text.

                [include:document-list]: ./ ({"level": 0})
                MARKDOWN,
            ],
            'Level `<number>`'                       => [
                <<<'MARKDOWN'
                #### [`<` Document B `>`](<Document B.md>)

                Summary text.

                [Read more](<Document B.md>).

                #### [Document A](<Document A.md>)

                Summary text with special characters `<`, `>`, `&`.

                [Read more](<Document A.md>).

                MARKDOWN,
                'Document.md',
                <<<'MARKDOWN'
                # Header

                Text text.

                [include:document-list]: ./ ({"level": 4})
                MARKDOWN,
            ],
        ];
    }
    //</editor-fold>
}
