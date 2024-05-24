<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use Generator;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers\DirectoryResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use Override;
use SplFileInfo;

use function strcmp;
use function usort;

/**
 * Returns the list of `*.md` files in the `<target>` directory. Each file
 * must have `# Header` as the first construction. The first paragraph
 * after the Header will be used as a summary.
 *
 * @implements InstructionContract<Directory, Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly PackageViewer $viewer,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:document-list';
    }

    #[Override]
    public static function getResolver(): string {
        return DirectoryResolver::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return Parameters::class;
    }

    /**
     * @return Generator<mixed, SplFileInfo|File|string, ?File, string>
     */
    #[Override]
    public function __invoke(Context $context, mixed $target, mixed $parameters): Generator {
        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $files     = $target->getFilesIterator('*.md', $parameters->depth);
        $self      = $context->file->getPath();

        foreach ($files as $file) {
            // Same?
            if ($self === $file->getPath()) {
                continue;
            }

            // Content?
            $file    = yield $file;
            $content = $file?->getContent();

            if (!$file || !$content) {
                continue;
            }

            // Title?
            $docTitle = Markdown::getTitle($content);

            if (!$docTitle) {
                throw new DocumentTitleIsMissing($context, $file);
            }

            // Add
            $documents[] = [
                'path'    => $file->getRelativePath($context->file),
                'title'   => $docTitle,
                'summary' => Markdown::getSummary($content),
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
