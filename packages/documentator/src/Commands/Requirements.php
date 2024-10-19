<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use Exception;
use Illuminate\Console\Command;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJsonFactory;
use LastDragon_ru\LaraASP\Documentator\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Metadata\Storage;
use LastDragon_ru\LaraASP\Documentator\Package;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Utils\Git;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use LastDragon_ru\LaraASP\Documentator\Utils\Version;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_combine;
use function array_diff_key;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_search;
use function array_unique;
use function array_values;
use function count;
use function end;
use function explode;
use function file_get_contents;
use function getcwd;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function range;
use function reset;
use function str_starts_with;
use function strtr;
use function trim;
use function uksort;
use function usort;

#[AsCommand(
    name       : Requirements::Name,
    description: 'Generates a table with the required versions of PHP/Laravel/etc in Markdown format.',
)]
class Requirements extends Command {
    public const  Name = Package::Name.':requirements';
    private const HEAD = 'HEAD';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    public $signature = self::Name.<<<'SIGNATURE'
        {cwd? : working directory (should be a git repository)}
    SIGNATURE;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $help = <<<'HELP'
        Requirements will be cached into `<cwd>/metadata.json`. You can also use
        this file to specify the required requirements. For example, to include
        PHP only:

        ```json
        {
            "require": {
                "php": "PHP"
            }
        }
        ```

        You can also merge multiple requirements into one. For example, the
        following will merge all `illuminate` into `laravel/framework` (the
        package will be ignored if not listed in `require`):

        ```json
        {
            "merge": {
                "illuminate/*": "laravel/framework"
            }
        }
        ```

        Defaults is:

        ```json
        {
            "require": {
                "php": "PHP",
                "laravel/framework": "Laravel"
            },
            "merge": {
                "illuminate/*": "laravel/framework"
            }
        }
        ```
        HELP;

    public function __construct(
        protected readonly Sorter $sorter,
    ) {
        parent::__construct();
    }

    public function __invoke(
        PackageViewer $viewer,
        ComposerJsonFactory $factory,
        Git $git,
        Serializer $serializer,
    ): void {
        // Get Versions
        $cwd  = new DirectoryPath(Cast::toString($this->argument('cwd') ?? getcwd()));
        $tags = $this->getPackageVersions($git, $cwd, [self::HEAD]);

        // Collect requirements
        $storage  = new Storage($serializer, $this->sorter, $cwd);
        $metadata = $storage->load();
        $packages = $metadata->require !== [] ? $metadata->require : [
            'php'               => 'PHP',
            'laravel/framework' => 'Laravel',
        ];
        $merge    = $metadata->merge ?? [
            'illuminate/*' => 'laravel/framework',
        ];

        foreach ($tags as $tag) {
            // Cached?
            if ($tag !== self::HEAD) {
                $cached = $metadata->requirements[$tag] ?? [];

                if (array_diff_key($packages, $cached) === [] && array_diff_key($cached, $packages) === []) {
                    continue;
                }
            }

            // Load
            $package = $this->getPackageInfo($factory, $git, $tag, $cwd);

            if ($package === null) {
                break;
            }

            // Update
            $metadata->requirements[$tag] = $this->getPackageRequirements($packages, $merge, $package);
        }

        // Cleanup
        foreach ($metadata->requirements as $tag => $requirement) {
            if (!isset($tags[$tag])) {
                unset($metadata->requirements[$tag]);
                break;
            }

            foreach ($requirement as $package => $versions) {
                if (!isset($packages[$package])) {
                    unset($metadata->requirements[$tag][$package]);
                }
            }
        }

        // Unreleased
        if (
            $metadata->version !== null
            && $metadata->version !== ''
            && !isset($metadata->requirements[$metadata->version])
            && isset($metadata->requirements[self::HEAD])
        ) {
            $metadata->requirements[$metadata->version] = $metadata->requirements[self::HEAD];
        }

        // Save
        $storage->save($metadata);

        // Prepare
        $requirements = $this->getRequirements($packages, $metadata);

        // Render
        $output = $viewer->render('requirements.default', [
            'packages'     => $packages,
            'requirements' => $requirements,
        ]);
        $output = trim($output);

        $this->output->writeln($output);
    }

    /**
     * @param list<string> $tags
     *
     * @return array<string, string>
     */
    protected function getPackageVersions(Git $git, DirectoryPath $cwd, array $tags = []): array {
        $tags = array_merge($tags, $git->getTags(Version::isVersion(...), $cwd));
        $tags = array_unique($tags);
        $tags = array_combine($tags, $tags);

        uksort($tags, $this->sorter->forVersion(SortOrder::Desc));

        return $tags;
    }

    protected function getPackageInfo(ComposerJsonFactory $factory, Git $git, string $tag, DirectoryPath $cwd): ?ComposerJson {
        try {
            $root    = (string) $git->getRoot($cwd);
            $path    = (string) $cwd->getFilePath('composer.json');
            $gitPath = str_starts_with($path, $root)
                ? mb_substr($path, mb_strlen($root) + 1)
                : $path;
            $package = $tag !== self::HEAD
                ? $git->getFile($gitPath, $tag, $cwd)
                : (string) file_get_contents($path);
            $package = $factory->createFromJson($package);
        } catch (Exception) {
            $package = null;
        }

        return $package;
    }

    /**
     * @param array<string, string> $require
     * @param array<string, string> $merge
     *
     * @return array<string, list<string>>
     */
    protected function getPackageRequirements(array $require, array $merge, ComposerJson $package): array {
        // Prepare
        $regexps = [];

        foreach ($require as $key => $title) {
            $regexp           = '#^'.preg_quote($key).'$#u';
            $regexps[$regexp] = $key;
        }

        foreach ($merge as $pattern => $key) {
            $regexp           = '#^'.strtr(preg_quote($pattern), ['\\*' => '.+?']).'$#u';
            $regexps[$regexp] = $key;
        }

        // Requirements
        $requirements = [];

        foreach ($package->require as $requirement => $constraint) {
            // Match?
            $match = false;

            foreach ($regexps as $regexp => $key) {
                if (preg_match($regexp, $requirement) > 0) {
                    $requirement = $key;
                    $match       = true;
                    break;
                }
            }

            if (!$match) {
                continue;
            }

            // Wanted?
            if (!isset($require[$requirement])) {
                continue;
            }

            // Add
            $required                   = explode('|', Cast::toString($constraint));
            $required                   = array_map(trim(...), $required);
            $required                   = array_filter($required, static fn ($string) => $string !== '');
            $required                   = array_values($required);
            $requirement                = Cast::toString($requirement);
            $requirements[$requirement] = array_merge($requirements[$requirement] ?? [], $required);
            $requirements[$requirement] = array_values(array_unique($requirements[$requirement]));

            usort($requirements[$requirement], $this->sorter->forString(SortOrder::Asc));
        }

        return $requirements;
    }

    /**
     * @param array<string, string> $packages
     *
     * @return array<string, array<string, list<string|array{string, string}>>>
     */
    protected function getRequirements(array $packages, Metadata $metadata): array {
        // Extract
        $requirements = [];

        foreach ($metadata->requirements as $versionName => $versionPackages) {
            foreach ($versionPackages as $packageName => $packageVersions) {
                foreach ($packageVersions as $packageVersion) {
                    $requirements[$packageName][$packageVersion] ??= [];
                    $requirements[$packageName][$packageVersion][] = $versionName;
                }
            }
        }

        // Sort
        $priorities = array_combine(array_keys($packages), range(0, count($packages) - 1));

        uksort($requirements, static function (string $a, string $b) use ($priorities): int {
            return $priorities[$a] <=> $priorities[$b];
        });

        // Merge
        $versions = array_keys($metadata->requirements);

        foreach ($requirements as &$packageVersions) {
            foreach ($packageVersions as &$versionNames) {
                $versionNames = $this->getMergedVersions($versions, $versionNames);
            }
        }

        // Return
        return $requirements;
    }

    /**
     * @param list<string> $versions
     * @param list<string> $merge
     *
     * @return list<string|array{string, string}>
     */
    protected function getMergedVersions(array $versions, array $merge): array {
        // Group
        $ranges  = [];
        $current = [];

        foreach ($merge as $key => $version) {
            $index       = array_search($version, $versions, true);
            $nextMerge   = $merge[$key + 1] ?? null;
            $nextVersion = $index !== false ? ($versions[$index + 1] ?? null) : null;

            $current[] = $version;

            if ($nextMerge !== $nextVersion) {
                $ranges[] = $current;
                $current  = [];
            }
        }

        if ($current !== []) {
            $ranges[] = $current;
        }

        // Merge
        $merged = [];

        foreach ($ranges as $range) {
            if (count($range) > 2) {
                $merged[] = [reset($range), end($range)];
            } elseif (count($range) === 2) {
                $merged[] = reset($range);
                $merged[] = end($range);
            } else {
                $merged[] = reset($range);
            }
        }

        return $merged;
    }
}
