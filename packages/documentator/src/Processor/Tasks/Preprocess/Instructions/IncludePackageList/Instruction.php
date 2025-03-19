<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Composer\Package as ComposerPackage;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Summary;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Title;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\Unlink;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\DirectoryIterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\Optional;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Instruction as IncludeDocumentList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function mb_trim;
use function str_replace;
use function trigger_deprecation;
use function usort;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '%{VERSION}', 'Please use `%s` instead.', IncludeDocumentList::class);

/**
 * Generates package list from `<target>` directory. The readme file will be
 * used to determine package name and summary.
 *
 * @deprecated %{VERSION} Please use {@see IncludeDocumentList} instead.
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

    /**
     * @return Generator<mixed, Dependency<*>, mixed, string>
     */
    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Generator {
        /** @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages */
        $packages    = [];
        $directory   = $context->file->getDirectoryPath($parameters->target);
        $directories = Cast::to(Iterator::class, yield new DirectoryIterator($directory, null, 0));

        foreach ($directories as $package) {
            // Prepare
            $package = Cast::to(Directory::class, $package);

            // Package?
            $packageFile = Cast::to(File::class, yield new FileReference($package->getFilePath('composer.json')));
            $packageInfo = $packageFile->as(ComposerPackage::class)->json;

            // Readme
            $readme  = $package->getFilePath(Cast::toString($packageInfo->readme ?? 'README.md'));
            $readme  = Cast::to(File::class, yield new FileReference($readme));
            $content = $readme->as(Document::class);

            if ($content->isEmpty()) {
                throw new PackageReadmeIsEmpty($context, $parameters, $package->getName());
            }

            // Add
            $move       = new Move($context->file->getFilePath($readme->getName()));
            $title      = mb_trim((string) $content->mutate(new Title(), new Unlink()));
            $title      = mb_trim(str_replace("\n", ' ', Utils::getHeadingText($title)));
            $title      = $title === '' ? Text::getPathTitle($package->getName()) : $title;
            $summary    = mb_trim((string) $content->mutate(new Summary())->mutate($move));
            $upgrade    = $package->getFilePath('UPGRADE.md');
            $upgrade    = Cast::toNullable(File::class, yield new Optional(new FileReference($upgrade)));
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
