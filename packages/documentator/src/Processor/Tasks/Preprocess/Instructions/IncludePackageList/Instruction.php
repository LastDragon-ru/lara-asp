<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

// @phpcs:disable Generic.Files.LineLength.TooLong

use Exception;
use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\DirectoriesIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Utils\Markdown;
use Override;

use function assert;
use function is_array;
use function is_file;
use function json_decode;
use function strcmp;
use function usort;

use const JSON_THROW_ON_ERROR;

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
 *
 * @implements InstructionContract<Parameters>
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
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, string>
     */
    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): Generator {
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $directories = Cast::to(Iterator::class, yield new DirectoriesIterator($target, null, 0));

        foreach ($directories as $package) {
            // Prepare
            $package = Cast::to(Directory::class, $package);

            // Package?
            $packageFile = Cast::to(File::class, yield new FileReference($package->getPath('composer.json')));
            $packageInfo = $this->getPackageInfo($packageFile);

            if (!$packageInfo) {
                throw new PackageComposerJsonIsMissing($context, $package);
            }

            // Readme
            $readme  = $package->getPath(Cast::toString($packageInfo['readme'] ?: 'README.md'));
            $readme  = Cast::to(File::class, yield new FileReference($readme));
            $content = $readme->getContent();

            if (!$content) {
                throw new PackageReadmeIsEmpty($context, $package, $readme);
            }

            // Title?
            $packageTitle = Markdown::getTitle($content);

            if (!$packageTitle) {
                throw new PackageReadmeTitleIsMissing($context, $package, $readme);
            }

            // Add
            $upgrade    = $package->getPath('UPGRADE.md');
            $upgrade    = is_file($upgrade)
                ? Path::getRelativePath($context->file->getPath(), $upgrade)
                : null;
            $packages[] = [
                'path'    => $readme->getRelativePath($context->file),
                'title'   => $packageTitle,
                'summary' => Markdown::getSummary($content),
                'upgrade' => $upgrade,
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
    protected function getPackageInfo(File $file): ?array {
        try {
            $package = $file->getContent();
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
