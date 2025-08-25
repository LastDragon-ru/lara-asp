<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Composer\Package as ComposerPackage;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Title;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\DirectoryIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\Optional;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function mb_trim;
use function str_replace;
use function trigger_deprecation;
use function usort;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '8.0.0', 'Please use `%s` instead.', IncludeDocumentList::class);

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
 *
 * @deprecated 8.0.0 Please use {@see IncludeDocumentList} instead.
 *
 * @implements InstructionContract<Parameters>
 */
readonly class Instruction implements InstructionContract {
    public function __construct(
        protected PackageViewer $viewer,
        protected Sorter $sorter,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:package-list';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Document|string {
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $directory   = $context->file->getDirectoryPath($parameters->target);
        $directories = $context->resolver->resolve(new DirectoryIterator($directory, depth: 0));

        foreach ($directories as $package) {
            // Prepare
            $package = Cast::to(Directory::class, $package);

            // Package?
            $packageFile = $context->resolver->resolve(new FileReference($package->getFilePath('composer.json')));
            $packageInfo = $packageFile->as(ComposerPackage::class)->json;

            // Readme
            $readme  = $package->getFilePath(Cast::toString($packageInfo->readme ?? 'README.md'));
            $readme  = $context->resolver->resolve(new FileReference($readme));
            $content = $readme->as(Document::class);

            // Add
            $move       = new Move($context->file->getFilePath($readme->getName()));
            $title      = mb_trim((string) $content->mutate(new Title()));
            $title      = mb_trim(str_replace("\n", ' ', $title));
            $title      = $title === '' ? Text::getPathTitle($package->getName()) : $title;
            $summary    = mb_trim((string) $content->mutate(new Summary())->mutate($move));
            $upgrade    = $package->getFilePath('UPGRADE.md');
            $upgrade    = $context->resolver->resolve(new Optional(new FileReference($upgrade)));
            $packages[] = [
                'path'    => $context->file->getRelativePath($readme),
                'title'   => $title,
                'summary' => $summary,
                'upgrade' => $upgrade !== null
                    ? $context->file->getRelativePath($upgrade)
                    : null,
            ];
        }

        // Packages?
        if ($packages === []) {
            return '';
        }

        // Sort
        $comparator = $this->sorter->forString($parameters->order);

        usort($packages, static function (array $a, $b) use ($comparator): int {
            return $comparator($a['title'], $b['title']);
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
