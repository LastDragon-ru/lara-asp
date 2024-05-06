<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use Exception;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use Override;
use Symfony\Component\Finder\Finder;

use function assert;
use function basename;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function strcmp;
use function usort;

use const JSON_THROW_ON_ERROR;

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
 *
 * @implements InstructionContract<string, Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly PackageViewer $viewer,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:package-list';
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
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $basePath    = basename($target);
        $directories = Finder::create()
            ->ignoreVCSIgnored(true)
            ->in($target)
            ->depth(0)
            ->exclude('vendor')
            ->exclude('node_modules')
            ->directories();

        foreach ($directories as $package) {
            // Package?
            $packagePath = $package->getPathname();
            $packageInfo = $this->getPackageInfo($packagePath);

            if (!$packageInfo) {
                throw new PackageComposerJsonIsMissing(
                    $context->path,
                    $context->target,
                    Path::join($basePath, $package->getFilename()),
                );
            }

            // Readme
            $readme  = $this->getPackageReadme($packagePath, $packageInfo);
            $content = $readme
                ? file_get_contents(Path::join($packagePath, $readme))
                : false;

            if (!$readme || $content === false) {
                throw new PackageReadmeIsMissing(
                    $context->path,
                    $context->target,
                    Path::join($basePath, $package->getFilename()),
                );
            }

            // Extract
            $packageTitle = Markdown::getTitle($content);
            $readmePath   = Path::join($basePath, $package->getFilename(), $readme);

            if ($packageTitle) {
                $upgrade     = $this->getPackageUpgrade($packagePath, $packageInfo);
                $upgradePath = $upgrade
                    ? Path::join($basePath, $package->getFilename(), $upgrade)
                    : null;

                $packages[] = [
                    'path'    => $readmePath,
                    'title'   => $packageTitle,
                    'summary' => Markdown::getSummary($content),
                    'upgrade' => $upgradePath,
                ];
            } else {
                throw new DocumentTitleIsMissing($context->path, $context->target, $readmePath);
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
        $template = "package-list.{$parameters->template}";
        $list     = $this->viewer->render($template, [
            'packages' => $packages,
        ]);

        // Return
        return $list;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    protected function getPackageInfo(string $path): ?array {
        try {
            $file    = Path::join($path, 'composer.json');
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
        return $this->getPackageFile($path, [
            $package['readme'] ?? null,
            'README.md',
        ]);
    }

    /**
     * @param array<array-key, mixed> $package
     */
    protected function getPackageUpgrade(string $path, array $package): ?string {
        return $this->getPackageFile($path, [
            'UPGRADE.md',
        ]);
    }

    /**
     * @param array<array-key, mixed> $variants
     */
    private function getPackageFile(string $path, array $variants): ?string {
        $file = null;

        foreach ($variants as $variant) {
            if ($variant && is_string($variant) && is_file(Path::getPath($path, $variant))) {
                $file = $variant;
                break;
            }
        }

        return $file;
    }
}
