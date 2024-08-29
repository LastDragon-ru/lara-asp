<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\DirectoriesIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\Optional;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Composer;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function strcmp;
use function usort;

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly PackageViewer $viewer,
        protected readonly Markdown $markdown,
        protected readonly Composer $composer,
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
            $packageInfo = $packageFile->getMetadata($this->composer);

            if (!$packageInfo) {
                throw new PackageComposerJsonIsMissing($context, $package);
            }

            // Readme
            $readme  = $package->getPath(Cast::toString($packageInfo->readme ?: 'README.md'));
            $readme  = Cast::to(File::class, yield new FileReference($readme));
            $content = $readme->getMetadata($this->markdown);

            if (!$content || $content->isEmpty()) {
                throw new PackageReadmeIsEmpty($context, $package, $readme);
            }

            // Add
            $content    = $context->toSplittable($content);
            $upgrade    = $package->getPath('UPGRADE.md');
            $upgrade    = Cast::toNullable(File::class, yield new Optional(new FileReference($upgrade)));
            $packages[] = [
                'path'    => $readme->getRelativePath($context->file),
                'title'   => $content->getTitle() ?? Text::getPathTitle("{$package->getName()}.md"),
                'summary' => $content->getSummary(),
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
}
