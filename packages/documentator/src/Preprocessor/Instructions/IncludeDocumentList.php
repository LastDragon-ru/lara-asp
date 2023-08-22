<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Symfony\Component\Finder\Finder;

use function file_get_contents;
use function strcmp;
use function usort;
use function view;

class IncludeDocumentList implements Instruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:document-list';
    }

    public function process(string $path, string $target): string {
        /** @var list<array{path: string, title: string, summary: ?string}> $documents */
        $documents = [];
        $files     = Finder::create()
            ->in(Path::getPath($path, $target))
            ->depth(0)
            ->name('*.md')
            ->files();

        foreach ($files as $file) {
            // Package?
            $content = file_get_contents($file->getPathname());

            if (!$content) {
                continue;
            }

            // Extract
            $title = Markdown::getTitle($content);

            if ($title) {
                $documents[] = [
                    'path'    => Path::normalize("{$target}/{$file->getFilename()}"),
                    'title'   => $title,
                    'summary' => Markdown::getSummary($content),
                ];
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
        $package = Package::Name;
        $list    = view("{$package}::document-list.markdown", [
            'documents' => $documents,
        ])->render();

        // Return
        return $list;
    }
}
