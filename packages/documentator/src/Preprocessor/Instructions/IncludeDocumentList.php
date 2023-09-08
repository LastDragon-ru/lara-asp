<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Symfony\Component\Finder\Finder;

use function basename;
use function dirname;
use function file_get_contents;
use function is_dir;
use function strcmp;
use function usort;

class IncludeDocumentList implements Instruction {
    public function __construct(
        protected readonly PackageViewer $viewer,
    ) {
        // empty
    }

    public static function getName(): string {
        return 'include:document-list';
    }

    public static function getDescription(): string {
        return <<<'DESC'
            Returns the list of `*.md` files in the `<target>` directory. Each file
            must have `# Header` as the first construction. The first paragraph
            after the Header will be used as a summary.
            DESC;
    }

    public static function getTargetDescription(): ?string {
        return 'Directory path.';
    }

    public function process(string $path, string $target): string {
        // Directory?
        $root = Path::getPath(dirname($path), $target);

        if (!is_dir($root)) {
            throw new TargetIsNotDirectory($path, $target);
        }

        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $target    = Path::normalize($target);
        $files     = Finder::create()
            ->in($root)
            ->depth(0)
            ->name('*.md')
            ->files();

        foreach ($files as $file) {
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
            $title = Markdown::getTitle($content);

            if ($title) {
                $documents[] = [
                    'path'    => Path::join($target, $file->getFilename()),
                    'title'   => $title,
                    'summary' => Markdown::getSummary($content),
                ];
            } else {
                throw new DocumentTitleIsMissing($path, $target, Path::join($target, $file->getFilename()));
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
        $list = $this->viewer->render('document-list.markdown', [
            'documents' => $documents,
        ]);

        // Return
        return $list;
    }
}