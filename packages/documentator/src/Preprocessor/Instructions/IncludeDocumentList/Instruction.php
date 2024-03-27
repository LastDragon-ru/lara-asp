<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use Symfony\Component\Finder\Finder;

use function basename;
use function dirname;
use function file_get_contents;
use function is_dir;
use function strcmp;
use function usort;

/**
 * @implements ParameterizableInstruction<Parameters>
 */
class Instruction implements ParameterizableInstruction {
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
    public static function getDescription(): string {
        return <<<'DESC'
            Returns the list of `*.md` files in the `<target>` directory. Each file
            must have `# Header` as the first construction. The first paragraph
            after the Header will be used as a summary.
            DESC;
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'Directory path.';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getParametersDescription(): array {
        return [];
    }

    #[Override]
    public function process(string $path, string $target, Serializable $parameters): string {
        // Directory?
        $base = dirname($path);
        $root = Path::getPath($base, $target);

        if (!is_dir($root)) {
            throw new TargetIsNotDirectory($path, $target);
        }

        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $target    = Path::normalize($target);
        $finder    = Finder::create()->in($root)->name('*.md');

        if ($parameters->depth !== null) {
            $finder->depth($parameters->depth);
        }

        foreach ($finder->files() as $file) {
            // Same?
            if ($target === '' && $file->getFilename() === basename($path)) {
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
                throw new DocumentTitleIsMissing($path, $target, $docPath);
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
