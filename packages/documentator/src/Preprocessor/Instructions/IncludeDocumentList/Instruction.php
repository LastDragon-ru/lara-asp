<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use Override;
use Symfony\Component\Finder\Finder;

use function basename;
use function dirname;
use function file_get_contents;
use function strcmp;
use function usort;

/**
 * Returns the list of `*.md` files in the `<target>` directory. Each file
 * must have `# Header` as the first construction. The first paragraph
 * after the Header will be used as a summary.
 *
 * @implements InstructionContract<Parameters, string, DirectoryPath<Parameters>>
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
    public static function getTarget(): string {
        return DirectoryPath::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return Parameters::class;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $path      = basename($context->path);
        $base      = dirname($context->path);
        $root      = $target;
        $target    = Path::normalize($context->target);
        $finder    = Finder::create()->in($root)->name('*.md');

        if ($parameters->depth !== null) {
            $finder->depth($parameters->depth);
        }

        foreach ($finder->files() as $file) {
            // Same?
            if ($target === '' && $file->getFilename() === $path) {
                continue;
            }

            // Content?
            $content = file_get_contents($file->getPathname());

            if (!$content) {
                continue;
            }

            // Extract
            $docTitle = Markdown::getTitle($content);
            $docPath  = Path::getRelativePath($base, $file->getPathname());

            if ($docTitle) {
                $documents[] = [
                    'path'    => $docPath,
                    'title'   => $docTitle,
                    'summary' => Markdown::getSummary($content),
                ];
            } else {
                throw new DocumentTitleIsMissing($context->path, $context->target, $docPath);
            }
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
