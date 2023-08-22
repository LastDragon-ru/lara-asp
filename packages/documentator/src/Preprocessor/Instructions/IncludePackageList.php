<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Symfony\Component\Finder\Finder;

use function assert;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function strcmp;
use function usort;
use function view;

use const JSON_THROW_ON_ERROR;

class IncludePackageList implements Instruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:package-list';
    }

    public function process(string $path, string $target): string {
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $directories = Finder::create()
            ->ignoreVCSIgnored(true)
            ->in(Path::getPath($path, $target))
            ->depth(0)
            ->exclude('vendor')
            ->exclude('node_modules')
            ->directories();

        foreach ($directories as $directory) {
            // Package?
            $root    = $directory->getPathname();
            $package = $this->getPackageInfo($root);

            if (!$package) {
                continue;
            }

            // Readme
            $readme  = $this->getPackageReadme($root, $package);
            $content = $readme
                ? file_get_contents("{$root}/{$readme}")
                : false;

            if ($content === false) {
                continue;
            }

            // Extract
            $title = Markdown::getTitle($content);

            if ($title) {
                $packages[] = [
                    'path'    => Path::normalize("{$target}/{$directory->getFilename()}/{$readme}"),
                    'title'   => $title,
                    'summary' => Markdown::getSummary($content),
                ];
            }
        }

        // Packages?
        if (!$packages) {
            return '';
        }

        // Sort
        usort($packages, static function (array $a, $b): int {
            return strcmp($a['title'], $b['title']);
        });

        // Render
        $package = Package::Name;
        $list    = view("{$package}::package-list.markdown", [
            'packages' => $packages,
        ])->render();

        // Return
        return $list;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    protected function getPackageInfo(string $path): ?array {
        try {
            $file    = "{$path}/composer.json";
            $package = is_file($file) ? file_get_contents($file) : false;
            $package = $package !== false
                ? json_decode($package, true, flags: JSON_THROW_ON_ERROR)
                : null;

            assert(is_array($package));
        } catch (Exception) {
            $package = null;
        }

        return $package;
    }

    /**
     * @param array<array-key, mixed> $package
     */
    protected function getPackageReadme(string $path, array $package): ?string {
        $readme   = null;
        $variants = [
            $package['readme'] ?? null,
            'README.md',
        ];

        foreach ($variants as $variant) {
            if ($variant && is_string($variant) && is_file(Path::getPath($path, $variant))) {
                $readme = $variant;
                break;
            }
        }

        return $readme;
    }
}
