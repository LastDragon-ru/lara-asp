<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function strcmp;
use function usort;

/**
 * Returns the list of `*.md` files in the `<target>` directory. Each file
 * must have `# Header` as the first construction. The first paragraph
 * after the Header will be used as a summary.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly PackageViewer $viewer,
        protected readonly Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:document-list';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, string>
     */
    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): Generator {
        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $iterator  = Cast::to(Iterator::class, yield new FileIterator($target, '*.md', $parameters->depth));
        $self      = $context->file->getPath();

        foreach ($iterator as $file) {
            // Prepare
            $file = Cast::to(File::class, $file);

            // Same?
            if ($self === $file->getPath()) {
                continue;
            }

            // Empty?
            $document = $file->getMetadata($this->markdown);

            if (!$document || $document->isEmpty()) {
                continue;
            }

            // Add
            $document    = $context->toSplittable($document);
            $documents[] = [
                'path'    => $file->getRelativePath($context->file),
                'title'   => $document->getTitle() ?? Text::getPathTitle($file->getName()),
                'summary' => $document->getSummary(),
            ];
        }

        // Empty?
        if (!$documents) {
            return '';
        }

        // Sort
        usort($documents, static function (array $a, $b): int {
            return strcmp($a['title'], $b['title']);
        });

        // Render
        $template = "document-list.{$parameters->template}";
        $list     = $this->viewer->render($template, [
            'documents' => $documents,
        ]);

        // Return
        return $list;
    }
}
