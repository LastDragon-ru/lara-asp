<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use Exception;
use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers\DirectoryResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use Override;
use SplFileInfo;

use function assert;
use function is_array;
use function json_decode;
use function strcmp;
use function usort;

use const JSON_THROW_ON_ERROR;

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
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
        return 'include:package-list';
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
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $directories = $target->getDirectoriesIterator(null, 0);

        foreach ($directories as $package) {
            // Package?
            $packageFile = yield $package->getPath('composer.json');
            $packageInfo = $this->getPackageInfo($packageFile);

            if (!$packageFile || !$packageInfo) {
                throw new PackageComposerJsonIsMissing($context, $package);
            }

            // Readme
            $readme  = yield $package->getPath(Cast::toString($packageInfo['readme'] ?: 'README.md'));
            $content = $readme?->getContent();

            if (!$readme || !$content) {
                throw new PackageReadmeIsMissing($context, $package);
            }

            // Title?
            $packageTitle = Markdown::getTitle($content);

            if (!$packageTitle) {
                throw new DocumentTitleIsMissing($context, $readme);
            }

            // Add
            $upgrade    = yield $package->getPath('UPGRADE.md');
            $packages[] = [
                'path'    => $readme->getRelativePath($context->file),
                'title'   => $packageTitle,
                'summary' => Markdown::getSummary($content),
                'upgrade' => $upgrade?->getRelativePath($context->file),
            ];
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
    protected function getPackageInfo(?File $file): ?array {
        try {
            $package = $file?->getContent();
            $package = $package
                ? json_decode($package, true, flags: JSON_THROW_ON_ERROR)
                : null;

            assert(is_array($package));
        } catch (Exception) {
            $package = null;
        }

        return $package;
    }
}
